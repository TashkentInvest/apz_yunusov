<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\ActualPayment;
use App\Models\PaymentSchedule;
use App\Models\Subject;
use App\Models\District;
use App\Services\NumberToTextService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $numberToText;

    public function __construct(NumberToTextService $numberToText)
    {
        $this->numberToText = $numberToText;
    }

    public function index(Request $request)
    {
        $period = $request->get('period', 'month');

        if ($request->get('ajax')) {
            $chartData = $this->getChartData($period);
            return response()->json($chartData);
        }

        // Calculate stats excluding cancelled
        $totalContracts = Contract::where('is_active', true)->count();

        $activeContracts = Contract::whereHas('status', function($q) {
            $q->where('name_uz', '!=', 'Бекор қилинган');
        })->where('is_active', true)->count();

        $totalAmount = Contract::where('is_active', true)
            ->whereHas('status', function($q) {
                $q->where('name_uz', '!=', 'Бекор қилинган');
            })
            ->sum('total_amount');

        $totalPaid = ActualPayment::whereHas('contract', function($q) {
            $q->where('is_active', true)
              ->whereHas('status', function($sq) {
                  $sq->where('name_uz', '!=', 'Бекор қилинган');
              });
        })->sum('amount');

        $debtorsCount = $this->getDebtorsCount();

        $stats = [
            'total_contracts' => $totalContracts,
            'active_contracts' => $activeContracts,
            'total_amount' => $totalAmount,
            'total_paid' => $totalPaid,
            'debtors_count' => $debtorsCount,
            'total_debt' => $totalAmount - $totalPaid
        ];

        $chartData = $this->buildChartData($period);
        $districtStats = $this->getDistrictStats();

        $recentContracts = Contract::with(['subject', 'status'])
            ->whereHas('status', function($q) {
                $q->where('name_uz', '!=', 'Бекор қилинган');
            })
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentPayments = ActualPayment::with(['contract.subject'])
            ->whereHas('contract', function($q) {
                $q->where('is_active', true)
                  ->whereHas('status', function($sq) {
                      $sq->where('name_uz', '!=', 'Бекор қилинган');
                  });
            })
            ->orderBy('payment_date', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'stats',
            'chartData',
            'districtStats',
            'recentContracts',
            'recentPayments',
            'period'
        ));
    }

    public function contractsByStatus(Request $request, $status)
    {
        $query = Contract::with(['subject', 'object.district', 'status', 'updatedBy'])
            ->where('is_active', true);

        switch($status) {
            case 'total':
                // All active contracts
                break;
            case 'active':
                $query->whereHas('status', function($q) {
                    $q->where('code', 'ACTIVE');
                });
                break;
            case 'paid':
                $query->whereHas('actualPayments');
                break;
            case 'debtors':
                $query->whereHas('paymentSchedules', function($q) {
                    $q->where('is_active', true);
                })->whereHas('status', function($q) {
                    $q->where('name_uz', '!=', 'Бекор қилинган');
                });
                break;
        }

        $totalAmount = (clone $query)
            ->whereHas('status', function($q) {
                $q->where('name_uz', '!=', 'Бекор қилинган');
            })
            ->sum('total_amount');

        $activeCount = (clone $query)->count();

        $contracts = $query->paginate(20)->appends($request->query());
        $statuses = \App\Models\ContractStatus::where('is_active', true)->get();
        $districts = \App\Models\District::where('is_active', true)
            ->where('name_uz', 'REGEXP', '^[А-Яа-яЎўҚқҒғҲҳ]')
            ->get();

        return view('contracts.index', compact('contracts', 'statuses', 'districts', 'totalAmount', 'activeCount'));
    }

    public function districtContracts(District $district)
    {
        $contracts = Contract::whereHas('object', function($q) use ($district) {
            $q->where('district_id', $district->id);
        })
        ->with(['subject', 'status', 'actualPayments', 'paymentSchedules', 'updatedBy'])
        ->where('is_active', true)
        ->whereHas('status', function($q) {
            $q->where('name_uz', '!=', 'Бекор қилинган');
        })
        ->paginate(20);

        $contractIds = Contract::whereHas('object', function($q) use ($district) {
            $q->where('district_id', $district->id);
        })->where('is_active', true)->pluck('id');

        $stats = [
            'total_contracts' => $contracts->total(),
            'legal_entities' => Contract::whereIn('id', $contractIds)
                ->whereHas('subject', function($q) {
                    $q->where('is_legal_entity', true);
                })->count(),
            'individuals' => Contract::whereIn('id', $contractIds)
                ->whereHas('subject', function($q) {
                    $q->where('is_legal_entity', false);
                })->count(),
            'total_amount' => Contract::whereIn('id', $contractIds)->sum('total_amount'),
            'total_paid' => ActualPayment::whereIn('contract_id', $contractIds)->sum('amount'),
        ];

        $stats['debt'] = $stats['total_amount'] - $stats['total_paid'];
        $stats['payment_percent'] = $stats['total_amount'] > 0 ? ($stats['total_paid'] / $stats['total_amount']) * 100 : 0;

        $chartData = $this->getDistrictChartData($district);
        $statuses = \App\Models\ContractStatus::where('is_active', true)->get();
        $districts = \App\Models\District::where('is_active', true)
            ->where('name_uz', 'REGEXP', '^[А-Яа-яЎўҚқҒғҲҳ]')
            ->get();

        return view('dashboard.district', compact('district', 'contracts', 'stats', 'chartData', 'statuses', 'districts'));
    }

    private function getDistrictChartData($district)
    {
        $now = Carbon::now();
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $year = $date->year;
            $month = $date->month;

            $actualAmount = ActualPayment::whereYear('payment_date', $year)
                ->whereMonth('payment_date', $month)
                ->whereHas('contract.object', function($q) use ($district) {
                    $q->where('district_id', $district->id);
                })
                ->sum('amount');

            $data[] = [
                'label' => $date->format('M Y'),
                'actual' => (float) $actualAmount,
            ];
        }

        return $data;
    }

    public function getChartData(Request $request)
    {
        $period = $request->get('period', 'month');
        $chartData = $this->buildChartData($period);
        return response()->json($chartData);
    }

    private function buildChartData($period)
    {
        $now = Carbon::now();
        $data = [];

        switch ($period) {
            case 'month':
                for ($i = 11; $i >= 0; $i--) {
                    $date = $now->copy()->subMonths($i);
                    $year = $date->year;
                    $month = $date->month;

                    $actualAmount = ActualPayment::whereYear('payment_date', $year)
                        ->whereMonth('payment_date', $month)
                        ->whereHas('contract', function($q) {
                            $q->where('is_active', true)
                              ->whereHas('status', function($sq) {
                                  $sq->where('name_uz', '!=', 'Бекор қилинган');
                              });
                        })
                        ->sum('amount');

                    $quarter = ceil($month / 3);
                    $plannedAmount = PaymentSchedule::where('year', $year)
                        ->where('quarter', $quarter)
                        ->where('is_active', true)
                        ->whereHas('contract', function($q) {
                            $q->where('is_active', true)
                              ->whereHas('status', function($sq) {
                                  $sq->where('name_uz', '!=', 'Бекор қилинган');
                              });
                        })
                        ->sum('quarter_amount') / 3;

                    $data[] = [
                        'label' => $date->format('M Y'),
                        'actual' => (float) $actualAmount,
                        'planned' => (float) $plannedAmount,
                    ];
                }
                break;

            case 'quarter':
                for ($i = 7; $i >= 0; $i--) {
                    $date = $now->copy()->subQuarters($i);
                    $year = $date->year;
                    $quarter = $date->quarter;

                    $actualAmount = ActualPayment::whereBetween('payment_date', [
                        Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->startOfDay(),
                        Carbon::create($year, $quarter * 3, 1)->endOfMonth()
                    ])
                    ->whereHas('contract', function($q) {
                        $q->where('is_active', true)
                          ->whereHas('status', function($sq) {
                              $sq->where('name_uz', '!=', 'Бекор қилинган');
                          });
                    })
                    ->sum('amount');

                    $plannedAmount = PaymentSchedule::where('year', $year)
                        ->where('quarter', $quarter)
                        ->where('is_active', true)
                        ->whereHas('contract', function($q) {
                            $q->where('is_active', true)
                              ->whereHas('status', function($sq) {
                                  $sq->where('name_uz', '!=', 'Бекор қилинган');
                              });
                        })
                        ->sum('quarter_amount');

                    $data[] = [
                        'label' => "Ч{$quarter} {$year}",
                        'actual' => (float) $actualAmount,
                        'planned' => (float) $plannedAmount,
                    ];
                }
                break;

            case 'year':
                for ($i = 4; $i >= 0; $i--) {
                    $year = $now->copy()->subYears($i)->year;

                    $actualAmount = ActualPayment::whereYear('payment_date', $year)
                        ->whereHas('contract', function($q) {
                            $q->where('is_active', true)
                              ->whereHas('status', function($sq) {
                                  $sq->where('name_uz', '!=', 'Бекор қилинган');
                              });
                        })
                        ->sum('amount');

                    $plannedAmount = PaymentSchedule::where('year', $year)
                        ->where('is_active', true)
                        ->whereHas('contract', function($q) {
                            $q->where('is_active', true)
                              ->whereHas('status', function($sq) {
                                  $sq->where('name_uz', '!=', 'Бекор қилинган');
                              });
                        })
                        ->sum('quarter_amount');

                    $data[] = [
                        'label' => (string)$year,
                        'actual' => (float) $actualAmount,
                        'planned' => (float) $plannedAmount,
                    ];
                }
                break;
        }

        return $data;
    }

    private function getDistrictStats()
    {
        $allDistricts = District::where('is_active', true)
            ->where('name_uz', 'REGEXP', '^[А-Яа-яЎўҚқҒғҲҳ]')
            ->orderBy('name_uz')
            ->get();

        $districtStats = collect();

        foreach ($allDistricts as $district) {
            $contracts = Contract::whereHas('object', function($q) use ($district) {
                $q->where('district_id', $district->id);
            })
            ->whereHas('status', function($q) {
                $q->where('name_uz', '!=', 'Бекор қилинган');
            })
            ->where('is_active', true)
            ->get();

            $contractIds = $contracts->pluck('id');
            $totalAmount = $contracts->sum('total_amount');
            $paidAmount = ActualPayment::whereIn('contract_id', $contractIds)->sum('amount');

            $districtStats->push((object)[
                'district_id' => $district->id,
                'district_name' => $district->name_uz,
                'contracts_count' => $contracts->count(),
                'total_amount' => (float) $totalAmount,
                'paid_amount' => (float) $paidAmount,
                'payment_percentage' => $totalAmount > 0 ? ($paidAmount / $totalAmount) * 100 : 0,
            ]);
        }

        return $districtStats->sortByDesc('contracts_count')->values();
    }

    private function getDebtorsCount()
    {
        $contractsWithDebt = Contract::with(['paymentSchedules', 'actualPayments'])
            ->whereHas('paymentSchedules', function($q) {
                $q->where('is_active', true);
            })
            ->whereHas('status', function($q) {
                $q->where('name_uz', '!=', 'Бекор қилинган');
            })
            ->where('is_active', true)
            ->get()
            ->filter(function($contract) {
                $totalScheduled = $contract->paymentSchedules->where('is_active', true)->sum('quarter_amount');
                $totalPaid = $contract->actualPayments->sum('amount');
                return $totalScheduled > $totalPaid;
            });

        return $contractsWithDebt->count();
    }

    public function export()
    {
        $contracts = Contract::with(['subject', 'object.district', 'status', 'actualPayments'])
            ->whereHas('status', function($q) {
                $q->where('name_uz', '!=', 'Бекор қилинган');
            })
            ->where('is_active', true)
            ->get();

        return response()->streamDownload(function () use ($contracts) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID',
                'Шартнома рақами',
                'Буюртмачи',
                'СТИР/ЖШШИР',
                'Туман',
                'Шартнома суммаси',
                'Тўланган',
                'Қолдиқ',
                'Прогресс (%)',
                'Ҳолати',
                'Шартнома санаси',
                'Тугаш санаси'
            ], ';');

            foreach ($contracts as $contract) {
                $totalPaid = $contract->actualPayments->sum('amount');
                $remaining = $contract->total_amount - $totalPaid;
                $progress = $contract->total_amount > 0 ? ($totalPaid / $contract->total_amount) * 100 : 0;

                fputcsv($handle, [
                    $contract->id,
                    $contract->contract_number,
                    $contract->subject->company_name ?? 'Кўрсатилмаган',
                    $contract->subject->is_legal_entity ? $contract->subject->inn : $contract->subject->pinfl,
                    $contract->object->district->name_uz ?? '',
                    $contract->total_amount,
                    $totalPaid,
                    $remaining,
                    number_format($progress, 2),
                    $contract->status->name_uz ?? '',
                    $contract->contract_date ? $contract->contract_date->format('d.m.Y') : '',
                    $contract->completion_date ? $contract->completion_date->format('d.m.Y') : '',
                ], ';');
            }

            fclose($handle);
        }, 'dashboard_report_' . now()->format('Y_m_d_H_i_s') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

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
         $contractIds = Contract::whereHas('object', function($q)  {
        })->where('is_active', true)->pluck('id');
        $stats = [
            'total_contracts' => $totalContracts,
            'legal_entities' => Contract::whereIn('id', $contractIds)
                            ->whereHas('subject', function($q) {
                                $q->where('is_legal_entity', true);
                            })->count(),
            'individuals' => Contract::whereIn('id', $contractIds)
                ->whereHas('subject', function($q) {
                    $q->where('is_legal_entity', false);
                })->count(),
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


    public function districtContracts(Request $request, District $district)
    {
        $period = $request->get('period', 'month');

        // Handle AJAX requests for chart data updates
        if ($request->get('ajax')) {
            $chartData = $this->getDistrictChartData($district, $period);
            return response()->json($chartData);
        }

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
        })->where('is_active', true)
          ->whereHas('status', function($q) {
              $q->where('name_uz', '!=', 'Бекор қилинган');
          })
          ->pluck('id');

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

        $chartData = $this->getDistrictChartData($district, $period);
        $statuses = \App\Models\ContractStatus::where('is_active', true)->get();
        $districts = \App\Models\District::where('is_active', true)
            ->where('name_uz', 'REGEXP', '^[А-Яа-яЎўҚқҒғҲҳ]')
            ->get();

        return view('dashboard.district', compact('district', 'contracts', 'stats', 'chartData', 'statuses', 'districts', 'period'));
    }

    private function getDistrictChartData($district, $period = 'month')
    {
        $now = Carbon::now();
        $data = [];

        switch ($period) {
            case 'month':
                for ($i = 11; $i >= 0; $i--) {
                    $date = $now->copy()->subMonths($i);
                    $year = $date->year;
                    $month = $date->month;

                    // Get contract IDs for this district (active and not cancelled)
                    $contractIds = Contract::where('is_active', true)
                        ->whereHas('status', function($q) {
                            $q->where('name_uz', '!=', 'Бекор қилинган');
                        })
                        ->whereHas('object', function($q) use ($district) {
                            $q->where('district_id', $district->id);
                        })
                        ->pluck('id');

                    // Get actual payments for these contracts
                    $actualAmount = ActualPayment::whereYear('payment_date', $year)
                        ->whereMonth('payment_date', $month)
                        ->whereIn('contract_id', $contractIds)
                        ->sum('amount');

                    // Get contract amounts signed in this period for this district
                    $contractAmount = Contract::whereYear('contract_date', $year)
                        ->whereMonth('contract_date', $month)
                        ->where('is_active', true)
                        ->whereHas('status', function($q) {
                            $q->where('name_uz', '!=', 'Бекор қилинган');
                        })
                        ->whereHas('object', function($q) use ($district) {
                            $q->where('district_id', $district->id);
                        })
                        ->sum('total_amount');

                    $data[] = [
                        'label' => $date->format('M Y'),
                        'actual' => (float) $actualAmount,
                        'planned' => (float) $contractAmount,
                    ];
                }
                break;

            case 'quarter':
                for ($i = 7; $i >= 0; $i--) {
                    $date = $now->copy()->subQuarters($i);
                    $year = $date->year;
                    $quarter = $date->quarter;

                    $contractIds = Contract::where('is_active', true)
                        ->whereHas('status', function($q) {
                            $q->where('name_uz', '!=', 'Бекор қилинган');
                        })
                        ->whereHas('object', function($q) use ($district) {
                            $q->where('district_id', $district->id);
                        })
                        ->pluck('id');

                    $actualAmount = ActualPayment::whereBetween('payment_date', [
                        Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->startOfDay(),
                        Carbon::create($year, $quarter * 3, 1)->endOfMonth()
                    ])
                    ->whereIn('contract_id', $contractIds)
                    ->sum('amount');

                    $contractAmount = Contract::whereBetween('contract_date', [
                        Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->startOfDay(),
                        Carbon::create($year, $quarter * 3, 1)->endOfMonth()
                    ])
                    ->where('is_active', true)
                    ->whereHas('status', function($q) {
                        $q->where('name_uz', '!=', 'Бекор қилинган');
                    })
                    ->whereHas('object', function($q) use ($district) {
                        $q->where('district_id', $district->id);
                    })
                    ->sum('total_amount');

                    $data[] = [
                        'label' => "Ч{$quarter} {$year}",
                        'actual' => (float) $actualAmount,
                        'planned' => (float) $contractAmount,
                    ];
                }
                break;

            case 'year':
                for ($i = 4; $i >= 0; $i--) {
                    $year = $now->copy()->subYears($i)->year;

                    $contractIds = Contract::where('is_active', true)
                        ->whereHas('status', function($q) {
                            $q->where('name_uz', '!=', 'Бекор қилинган');
                        })
                        ->whereHas('object', function($q) use ($district) {
                            $q->where('district_id', $district->id);
                        })
                        ->pluck('id');

                    $actualAmount = ActualPayment::whereYear('payment_date', $year)
                        ->whereIn('contract_id', $contractIds)
                        ->sum('amount');

                    $contractAmount = Contract::whereYear('contract_date', $year)
                        ->where('is_active', true)
                        ->whereHas('status', function($q) {
                            $q->where('name_uz', '!=', 'Бекор қилинган');
                        })
                        ->whereHas('object', function($q) use ($district) {
                            $q->where('district_id', $district->id);
                        })
                        ->sum('total_amount');

                    $data[] = [
                        'label' => (string)$year,
                        'actual' => (float) $actualAmount,
                        'planned' => (float) $contractAmount,
                    ];
                }
                break;
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

                    // Actual payments
                    $actualAmount = ActualPayment::whereYear('payment_date', $year)
                        ->whereMonth('payment_date', $month)
                        ->whereHas('contract', function($q) {
                            $q->where('is_active', true)
                              ->whereHas('status', function($sq) {
                                  $sq->where('name_uz', '!=', 'Бекор қилинган');
                              });
                        })
                        ->sum('amount');

                    // NEW: Show contract amounts instead of payment schedules
                    $contractAmount = Contract::whereYear('contract_date', $year)
                        ->whereMonth('contract_date', $month)
                        ->where('is_active', true)
                        ->whereHas('status', function($q) {
                            $q->where('name_uz', '!=', 'Бекор қилинган');
                        })
                        ->sum('total_amount');

                    $data[] = [
                        'label' => $date->format('M Y'),
                        'actual' => (float) $actualAmount,
                        'planned' => (float) $contractAmount,
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

                    $contractAmount = Contract::whereBetween('contract_date', [
                        Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->startOfDay(),
                        Carbon::create($year, $quarter * 3, 1)->endOfMonth()
                    ])
                    ->where('is_active', true)
                    ->whereHas('status', function($q) {
                        $q->where('name_uz', '!=', 'Бекор қилинган');
                    })
                    ->sum('total_amount');

                    $data[] = [
                        'label' => "Ч{$quarter} {$year}",
                        'actual' => (float) $actualAmount,
                        'planned' => (float) $contractAmount,
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

                    $contractAmount = Contract::whereYear('contract_date', $year)
                        ->where('is_active', true)
                        ->whereHas('status', function($q) {
                            $q->where('name_uz', '!=', 'Бекор қилинган');
                        })
                        ->sum('total_amount');

                    $data[] = [
                        'label' => (string)$year,
                        'actual' => (float) $actualAmount,
                        'planned' => (float) $contractAmount,
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
        // Count contracts that have payments less than total amount
        return Contract::where('is_active', true)
            ->whereHas('status', function($q) {
                $q->where('name_uz', '!=', 'Бекор қилинган');
            })
            ->where(function($q) {
                $q->whereDoesntHave('actualPayments')
                  ->orWhereRaw('(SELECT COALESCE(SUM(amount), 0) FROM actual_payments WHERE contract_id = contracts.id) < contracts.total_amount');
            })
            ->count();
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

//monitoring
public function monitoring(Request $request)
{
    $districts = District::where('is_active', true)
        ->where('name_uz', 'REGEXP', '^[А-Яа-яЎўҚқҒғҲҳ]')
        ->orderBy('name_uz')
        ->get();

    $monitoringData = [];
    $cityTotals = [
        'total_contracts' => 0,
        'total_amount' => 0,
        'apz_count' => 0,
        'gasn_count' => 0,
        'kengash_count' => 0,
        'permit_count' => 0,
        'expertise_count' => 0,
        'active_count' => 0,
        'cancelled_count' => 0,
        'completed_count' => 0,
        'cancelled_amount' => 0,
        'completed_amount' => 0,
        'active_amount' => 0,
        'returned_amount' => 0,
        'total_paid' => 0,
        'total_debt' => 0,
        'debt_2025' => 0,
    ];

    foreach ($districts as $district) {
        $contractIds = Contract::whereHas('object', function($q) use ($district) {
            $q->where('district_id', $district->id);
        })->where('is_active', true)->pluck('id');

        $allContracts = Contract::whereIn('id', $contractIds)->get();

        $activeContracts = $allContracts->filter(function($c) {
            return $c->status && $c->status->code === 'ACTIVE';
        });

        $cancelledContracts = $allContracts->filter(function($c) {
            return $c->status && $c->status->name_uz === 'Бекор қилинган';
        });

        $completedContracts = $allContracts->filter(function($c) {
            return $c->status && $c->status->code === 'COMPLETED';
        });

        $totalAmount = $allContracts->whereNotIn('status.name_uz', ['Бекор қилинган'])->sum('total_amount');

        // Calculate actual payments using the same method as in your contract display
        $totalPaid = ActualPayment::whereIn('contract_id', $contractIds)->sum('amount');

        $totalDebt = $totalAmount - $totalPaid;

        // Calculate amounts for each status
        $cancelledAmount = $cancelledContracts->sum('total_amount');
        $completedAmount = $completedContracts->sum('total_amount');
        $activeAmount = $activeContracts->sum('total_amount');

        // Debt for 2025
        $debt2025 = Contract::whereIn('id', $contractIds)
            ->whereYear('completion_date', 2025)
            ->whereHas('status', function($q) {
                $q->where('name_uz', '!=', 'Бекор қилинган');
            })
            ->get()
            ->sum(function($contract) {
                $paid = $contract->actualPayments->sum('amount');
                return max(0, $contract->total_amount - $paid);
            });

        // Count by permit types
        $apzCount = Contract::whereIn('id', $contractIds)
            ->whereHas('object', function($q) {
                $q->where('permit_type_id', 1);
            })->count();

        $gasnCount = Contract::whereIn('id', $contractIds)
            ->whereHas('object', function($q) {
                $q->where('permit_type_id', 2);
            })->count();

        $kengashCount = Contract::whereIn('id', $contractIds)
            ->whereHas('object', function($q) {
                $q->where('permit_type_id', 3);
            })->count();

        $permitCount = Contract::whereIn('id', $contractIds)
            ->whereHas('object', function($q) {
                $q->where('permit_type_id', 4);
            })->count();

        $expertiseCount = Contract::whereIn('id', $contractIds)
            ->whereHas('object', function($q) {
                $q->where('permit_type_id', 5);
            })->count();

        $districtData = [
            'district' => $district,
            'total_contracts' => $allContracts->count(),
            'total_amount' => $totalAmount,
            'apz_count' => $apzCount,
            'gasn_count' => $gasnCount,
            'kengash_count' => $kengashCount,
            'permit_count' => $permitCount,
            'expertise_count' => $expertiseCount,
            'active_count' => $activeContracts->count(),
            'cancelled_count' => $cancelledContracts->count(),
            'completed_count' => $completedContracts->count(),
            'cancelled_amount' => $cancelledAmount,
            'completed_amount' => $completedAmount,
            'active_amount' => $activeAmount,
            'returned_amount' => 0,
            'total_paid' => $totalPaid,
            'total_debt' => $totalDebt,
            'debt_2025' => $debt2025,
        ];

        $monitoringData[] = $districtData;

        // Add to city totals
        foreach ($cityTotals as $key => $value) {
            if ($key !== 'district') {
                $cityTotals[$key] += $districtData[$key];
            }
        }
    }

    return view('monitoring.index', compact('monitoringData', 'cityTotals'));
}
public function monitoringDistrict(District $district)
{

// dd('dasvf');
    $contracts = Contract::whereHas('object', function($q) use ($district) {
        $q->where('district_id', $district->id);
    })
    ->with(['subject', 'status', 'object.permitType', 'actualPayments'])
    // ->where('is_active', operator: true)
    ->paginate(50);

    return view('monitoring.district', compact('district', 'contracts'));
}

public function allDistricts(Request $request)
{
    $districts = District::where('is_active', true)
        ->where('name_uz', 'REGEXP', '^[А-Яа-яЎўҚқҒғҲҳ]')
        ->orderBy('name_uz')
        ->get();

    $monitoringData = [];
    $cityTotals = [
        'total_contracts' => 0,
        'total_amount' => 0,
        'apz_count' => 0,
        'gasn_count' => 0,
        'kengash_count' => 0,
        'permit_count' => 0,
        'expertise_count' => 0,
        'active_count' => 0,
        'cancelled_count' => 0,
        'completed_count' => 0,
        'returned_amount' => 0,
        'total_paid' => 0,
        'total_debt' => 0,
        'debt_2025' => 0,
    ];

    foreach ($districts as $district) {
        $contractIds = Contract::whereHas('object', function($q) use ($district) {
            $q->where('district_id', $district->id);
        })->where('is_active', true)->pluck('id');

        $allContracts = Contract::whereIn('id', $contractIds)->get();

        $activeContracts = $allContracts->filter(function($c) {
            return $c->status && $c->status->code === 'ACTIVE';
        });

        $cancelledContracts = $allContracts->filter(function($c) {
            return $c->status && $c->status->name_uz === 'Бекор қилинган';
        });

        $completedContracts = $allContracts->filter(function($c) {
            return $c->status && $c->status->code === 'COMPLETED';
        });

        $totalAmount = $allContracts->whereNotIn('status.name_uz', ['Бекор қилинган'])->sum('total_amount');
        $totalPaid = ActualPayment::whereIn('contract_id', $contractIds)->sum('amount');
        $totalDebt = $totalAmount - $totalPaid;

        $debt2025 = Contract::whereIn('id', $contractIds)
            ->whereYear('completion_date', 2025)
            ->whereHas('status', function($q) {
                $q->where('name_uz', '!=', 'Бекор қилинган');
            })
            ->get()
            ->sum(function($contract) {
                $paid = $contract->actualPayments->sum('amount');
                return max(0, $contract->total_amount - $paid);
            });

        $apzCount = Contract::whereIn('id', $contractIds)
            ->whereHas('object', function($q) {
                $q->where('permit_type_id', 1);
            })->count();

        $gasnCount = Contract::whereIn('id', $contractIds)
            ->whereHas('object', function($q) {
                $q->where('permit_type_id', 2);
            })->count();

        $kengashCount = Contract::whereIn('id', $contractIds)
            ->whereHas('object', function($q) {
                $q->where('permit_type_id', 3);
            })->count();

        $permitCount = Contract::whereIn('id', $contractIds)
            ->whereHas('object', function($q) {
                $q->where('permit_type_id', 4);
            })->count();

        $expertiseCount = Contract::whereIn('id', $contractIds)
            ->whereHas('object', function($q) {
                $q->where('permit_type_id', 5);
            })->count();

        $districtData = [
            'district' => $district,
            'total_contracts' => $allContracts->count(),
            'total_amount' => $totalAmount,
            'apz_count' => $apzCount,
            'gasn_count' => $gasnCount,
            'kengash_count' => $kengashCount,
            'permit_count' => $permitCount,
            'expertise_count' => $expertiseCount,
            'active_count' => $activeContracts->count(),
            'cancelled_count' => $cancelledContracts->count(),
            'completed_count' => $completedContracts->count(),
            'returned_amount' => 0,
            'total_paid' => $totalPaid,
            'total_debt' => $totalDebt,
            'debt_2025' => $debt2025,
        ];

        $monitoringData[] = $districtData;

        foreach ($cityTotals as $key => $value) {
            if ($key !== 'district') {
                $cityTotals[$key] += $districtData[$key];
            }
        }
    }

    return view('monitoring.districts', compact('monitoringData', 'cityTotals'));
}

public function contractsByPermitType(Request $request, $permitTypeId)
{
    $permitType = \App\Models\PermitType::findOrFail($permitTypeId);
    $districtId = $request->query('district');

    $query = Contract::whereHas('object', function($q) use ($permitTypeId, $districtId) {
        $q->where('permit_type_id', $permitTypeId);

        // Add district filter if provided
        if ($districtId) {
            $q->where('district_id', $districtId);
        }
    })
->with(['subject', 'object.district', 'status', 'actualPayments']);

    $contracts = $query->paginate(50);

    // Get district name if filtering by district
    $district = $districtId ? \App\Models\District::find($districtId) : null;

    // Calculate statistics
    $totalAmount = $contracts->sum('total_amount');
    $totalPaid = $contracts->sum(function($contract) {
        return $contract->actualPayments->sum('amount');
    });
    $totalDebt = $totalAmount - $totalPaid;

    return view('monitoring.permit-type', compact('contracts', 'permitType', 'district', 'totalAmount', 'totalPaid', 'totalDebt'));
}

public function contractsByStatus(Request $request, $statusType)
{
    $districtId = $request->query('district');

    $query = Contract::with(['subject', 'object.district', 'status', 'actualPayments'])
        ->where('is_active', true);

    // Add district filter if provided
    if ($districtId) {
        $query->whereHas('object', function($q) use ($districtId) {
            $q->where('district_id', $districtId);
        });
    }

    // Determine status based on type
    $statusName = '';
    switch($statusType) {
        case 'active':
            $query->whereHas('status', function($q) {
                $q->where('code', 'ACTIVE');
            });
            $statusName = 'Амалда';
            break;
        case 'cancelled':
            $query->whereHas('status', function($q) {
                $q->where('name_uz', 'Бекор қилинган');
            });
            $statusName = 'Бекор қилинган';
            break;
        case 'completed':
            $query->whereHas('status', function($q) {
                $q->where('code', 'COMPLETED');
            });
            $statusName = 'Якунланган';
            break;
    }

    $contracts = $query->paginate(50);

    // Get district name if filtering by district
    $district = $districtId ? \App\Models\District::find($districtId) : null;

    // Calculate statistics
    $totalAmount = $contracts->sum('total_amount');
    $totalPaid = $contracts->sum(function($contract) {
        return $contract->actualPayments->sum('amount');
    });
    $totalDebt = $totalAmount - $totalPaid;

    return view('monitoring.status', compact('contracts', 'statusName', 'statusType', 'district', 'totalAmount', 'totalPaid', 'totalDebt'));
}
}

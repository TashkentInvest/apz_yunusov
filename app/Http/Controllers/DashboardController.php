<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\ActualPayment;
use App\Models\PaymentSchedule;
use App\Models\Subject;
use App\Models\District;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month'); // month, quarter, year

        // If it's an AJAX request, return only chart data
        if ($request->get('ajax')) {
            $chartData = $this->getChartData($period);
            return response()->json($chartData);
        }

        // Calculate stats excluding cancelled and suspended contracts
        $totalContracts = Contract::where('is_active', true)->count();

        $activeContracts = Contract::whereHas('status', function($q) {
            $q->whereNotIn('code', ['CANCELLED', 'SUSPENDED']);
        })->where('is_active', true)->count();

        $totalAmount = Contract::where('is_active', true)
            ->whereHas('status', function($q) {
                $q->whereNotIn('code', ['CANCELLED', 'SUSPENDED']);
            })
            ->sum('total_amount');

        // Get actual payments sum only from active contracts (not cancelled/suspended)
        $totalPaid = ActualPayment::whereHas('contract', function($q) {
            $q->where('is_active', true)
              ->whereHas('status', function($sq) {
                  $sq->whereNotIn('code', ['CANCELLED', 'SUSPENDED']);
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

        // Get chart data based on period
        $chartData = $this->buildChartData($period);

        // Get all districts statistics
        $districtStats = $this->getDistrictStats();

        // Get recent contracts (exclude cancelled/suspended)
        $recentContracts = Contract::with(['subject', 'status'])
            ->whereHas('status', function($q) {
                $q->whereNotIn('code', ['CANCELLED', 'SUSPENDED']);
            })
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent payments from active contracts only
        $recentPayments = ActualPayment::with(['contract.subject'])
            ->whereHas('contract', function($q) {
                $q->where('is_active', true)
                  ->whereHas('status', function($sq) {
                      $sq->whereNotIn('code', ['CANCELLED', 'SUSPENDED']);
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
                // Last 12 months
                for ($i = 11; $i >= 0; $i--) {
                    $date = $now->copy()->subMonths($i);
                    $year = $date->year;
                    $month = $date->month;

                    // Actual payments from active contracts only
                    $actualAmount = ActualPayment::whereYear('payment_date', $year)
                        ->whereMonth('payment_date', $month)
                        ->whereHas('contract', function($q) {
                            $q->where('is_active', true)
                              ->whereHas('status', function($sq) {
                                  $sq->whereNotIn('code', ['CANCELLED', 'SUSPENDED']);
                              });
                        })
                        ->sum('amount');

                    // Planned payments (from active payment schedules)
                    $quarter = ceil($month / 3);
                    $plannedAmount = PaymentSchedule::where('year', $year)
                        ->where('quarter', $quarter)
                        ->where('is_active', true)
                        ->whereHas('contract', function($q) {
                            $q->where('is_active', true)
                              ->whereHas('status', function($sq) {
                                  $sq->whereNotIn('code', ['CANCELLED', 'SUSPENDED']);
                              });
                        })
                        ->sum('quarter_amount') / 3; // Divide by 3 to get monthly average

                    $data[] = [
                        'label' => $date->format('M Y'),
                        'actual' => (float) $actualAmount,
                        'planned' => (float) $plannedAmount,
                    ];
                }
                break;

            case 'quarter':
                // Last 8 quarters
                for ($i = 7; $i >= 0; $i--) {
                    $date = $now->copy()->subQuarters($i);
                    $year = $date->year;
                    $quarter = $date->quarter;

                    // Actual payments from active contracts
                    $actualAmount = ActualPayment::whereBetween('payment_date', [
                        Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->startOfDay(),
                        Carbon::create($year, $quarter * 3, 1)->endOfMonth()
                    ])
                    ->whereHas('contract', function($q) {
                        $q->where('is_active', true)
                          ->whereHas('status', function($sq) {
                              $sq->whereNotIn('code', ['CANCELLED', 'SUSPENDED']);
                          });
                    })
                    ->sum('amount');

                    // Planned payments from active schedules
                    $plannedAmount = PaymentSchedule::where('year', $year)
                        ->where('quarter', $quarter)
                        ->where('is_active', true)
                        ->whereHas('contract', function($q) {
                            $q->where('is_active', true)
                              ->whereHas('status', function($sq) {
                                  $sq->whereNotIn('code', ['CANCELLED', 'SUSPENDED']);
                              });
                        })
                        ->sum('quarter_amount');

                    $data[] = [
                        'label' => "Q{$quarter} {$year}",
                        'actual' => (float) $actualAmount,
                        'planned' => (float) $plannedAmount,
                    ];
                }
                break;

            case 'year':
                // Last 5 years
                for ($i = 4; $i >= 0; $i--) {
                    $year = $now->copy()->subYears($i)->year;

                    // Actual payments from active contracts
                    $actualAmount = ActualPayment::whereYear('payment_date', $year)
                        ->whereHas('contract', function($q) {
                            $q->where('is_active', true)
                              ->whereHas('status', function($sq) {
                                  $sq->whereNotIn('code', ['CANCELLED', 'SUSPENDED']);
                              });
                        })
                        ->sum('amount');

                    // Planned payments from active schedules
                    $plannedAmount = PaymentSchedule::where('year', $year)
                        ->where('is_active', true)
                        ->whereHas('contract', function($q) {
                            $q->where('is_active', true)
                              ->whereHas('status', function($sq) {
                                  $sq->whereNotIn('code', ['CANCELLED', 'SUSPENDED']);
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
        // Get all active districts
        $allDistricts = District::where('is_active', true)
            ->orderBy('name_ru')
            ->get();

        $districtStats = collect();

        foreach ($allDistricts as $district) {
            // Get active contracts for this district (exclude cancelled/suspended)
            $contracts = Contract::whereHas('object', function($q) use ($district) {
                $q->where('district_id', $district->id);
            })
            ->whereHas('status', function($q) {
                $q->whereNotIn('code', ['CANCELLED', 'SUSPENDED']);
            })
            ->where('is_active', true)
            ->get();

            $contractIds = $contracts->pluck('id');

            // Calculate totals
            $totalAmount = $contracts->sum('total_amount');
            $paidAmount = ActualPayment::whereIn('contract_id', $contractIds)->sum('amount');

            $districtStats->push((object)[
                'district_id' => $district->id,
                'district_name' => $district->name_ru,
                'contracts_count' => $contracts->count(),
                'total_amount' => (float) $totalAmount,
                'paid_amount' => (float) $paidAmount,
                'payment_percentage' => $totalAmount > 0 ? ($paidAmount / $totalAmount) * 100 : 0,
            ]);
        }

        // Sort by contracts count descending, then by total amount
        return $districtStats->sortByDesc('contracts_count')->values();
    }

    private function getDebtorsCount()
    {
        // Get contracts that have scheduled payments but haven't paid enough (exclude cancelled/suspended)
        $contractsWithDebt = Contract::with(['paymentSchedules', 'actualPayments'])
            ->whereHas('paymentSchedules', function($q) {
                $q->where('is_active', true);
            })
            ->whereHas('status', function($q) {
                $q->whereNotIn('code', ['CANCELLED', 'SUSPENDED']);
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

    public function getChartDataAjax(Request $request)
    {
        $period = $request->get('period', 'month');
        $chartData = $this->buildChartData($period);

        return response()->json($chartData);
    }

    public function export()
    {
        // Export only active contracts (exclude cancelled/suspended)
        $contracts = Contract::with(['subject', 'object.district', 'status', 'actualPayments'])
            ->whereHas('status', function($q) {
                $q->whereNotIn('code', ['CANCELLED', 'SUSPENDED']);
            })
            ->where('is_active', true)
            ->get();

        return response()->streamDownload(function () use ($contracts) {
            $handle = fopen('php://output', 'w');

            // Add BOM for proper UTF-8 encoding in Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Headers
            fputcsv($handle, [
                'ID',
                'Номер договора',
                'Заказчик',
                'ИНН/ПИНФЛ',
                'Район',
                'Сумма договора',
                'Оплачено',
                'Остаток',
                'Прогресс (%)',
                'Статус',
                'Дата договора',
                'Дата завершения'
            ], ';'); // Use semicolon for better Excel compatibility

            // Data
            foreach ($contracts as $contract) {
                $totalPaid = $contract->actualPayments->sum('amount');
                $remaining = $contract->total_amount - $totalPaid;
                $progress = $contract->total_amount > 0 ? ($totalPaid / $contract->total_amount) * 100 : 0;

                fputcsv($handle, [
                    $contract->id,
                    $contract->contract_number,
                    $contract->subject->display_name ?? $contract->subject->company_name,
                    $contract->subject->is_legal_entity ? $contract->subject->inn : $contract->subject->pinfl,
                    $contract->object->district->name_ru ?? '',
                    $contract->total_amount,
                    $totalPaid,
                    $remaining,
                    number_format($progress, 2),
                    $contract->status->name_ru ?? '',
                    $contract->contract_date ? $contract->contract_date->format('d.m.Y') : '',
                    $contract->completion_date ? $contract->completion_date->format('d.m.Y') : '',
                ], ';');
            }

            fclose($handle);
        }, 'dashboard_report_' . now()->format('Y_m_d_H_i_s') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function districtDetails(District $district)
    {
        // Get active contracts for this district (exclude cancelled/suspended)
        $contracts = Contract::whereHas('object', function($q) use ($district) {
            $q->where('district_id', $district->id);
        })
        ->whereHas('status', function($q) {
            $q->whereNotIn('code', ['CANCELLED', 'SUSPENDED']);
        })
        ->with(['subject', 'status', 'actualPayments'])
        ->where('is_active', true)
        ->get();

        $contractIds = $contracts->pluck('id');

        $stats = [
            'total_contracts' => $contracts->count(),
            'total_amount' => (float) $contracts->sum('total_amount'),
            'total_paid' => (float) ActualPayment::whereIn('contract_id', $contractIds)->sum('amount'),
        ];

        $stats['remaining'] = $stats['total_amount'] - $stats['total_paid'];
        $stats['progress'] = $stats['total_amount'] > 0 ? ($stats['total_paid'] / $stats['total_amount']) * 100 : 0;

        // Recent contracts in this district
        $recentContracts = $contracts->sortByDesc('created_at')->take(10);

        return response()->json([
            'district' => $district,
            'stats' => $stats,
            'contracts' => $recentContracts->values()
        ]);
    }
}

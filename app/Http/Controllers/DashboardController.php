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
        
        $stats = [
            'total_contracts' => Contract::where('is_active', true)->count(),
            'active_contracts' => Contract::whereHas('status', function($q) {
                $q->where('code', 'ACTIVE');
            })->count(),
            'total_amount' => Contract::where('is_active', true)->sum('total_amount'),
            'total_paid' => ActualPayment::sum('amount'),
            'debtors_count' => $this->getDebtorsCount()
        ];

        $stats['total_debt'] = $stats['total_amount'] - $stats['total_paid'];

        // Get chart data based on period
        $chartData = $this->buildChartData($period);
        
        // Get all districts statistics
        $districtStats = $this->getDistrictStats();

        // Get recent contracts
        $recentContracts = Contract::with(['subject', 'status'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent payments
        $recentPayments = ActualPayment::with(['contract.subject'])
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
                    
                    // Actual payments
                    $actualAmount = ActualPayment::whereYear('payment_date', $year)
                        ->whereMonth('payment_date', $month)
                        ->sum('amount');
                    
                    // Planned payments (from payment schedules)
                    $quarter = ceil($month / 3);
                    $plannedAmount = PaymentSchedule::where('year', $year)
                        ->where('quarter', $quarter)
                        ->sum('quarter_amount') / 3; // Divide by 3 to get monthly average
                    
                    $data[] = [
                        'label' => $date->format('M Y'),
                        'actual' => $actualAmount,
                        'planned' => $plannedAmount,
                    ];
                }
                break;
                
            case 'quarter':
                // Last 8 quarters
                for ($i = 7; $i >= 0; $i--) {
                    $date = $now->copy()->subQuarters($i);
                    $year = $date->year;
                    $quarter = $date->quarter;
                    
                    // Actual payments
                    $actualAmount = ActualPayment::where('year', $year)
                        ->where('quarter', $quarter)
                        ->sum('amount');
                    
                    // Planned payments
                    $plannedAmount = PaymentSchedule::where('year', $year)
                        ->where('quarter', $quarter)
                        ->sum('quarter_amount');
                    
                    $data[] = [
                        'label' => "Q{$quarter} {$year}",
                        'actual' => $actualAmount,
                        'planned' => $plannedAmount,
                    ];
                }
                break;
                
            case 'year':
                // Last 5 years
                for ($i = 4; $i >= 0; $i--) {
                    $year = $now->copy()->subYears($i)->year;
                    
                    // Actual payments
                    $actualAmount = ActualPayment::where('year', $year)->sum('amount');
                    
                    // Planned payments
                    $plannedAmount = PaymentSchedule::where('year', $year)->sum('quarter_amount');
                    
                    $data[] = [
                        'label' => (string)$year,
                        'actual' => $actualAmount,
                        'planned' => $plannedAmount,
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
        
        // Map Excel district names to database district names
        $districtMapping = [
            'Олмазор' => ['Алмазарский'],
            'Мирзо-Улуғбек' => ['Мирзо-Улугбекский', 'Мирзо Улугбекский'],
            'Яккасарой' => ['Яккасарайский'],
            'Шайхонтохур' => ['Шайхантахурский'],
            'Сергели' => ['Сергелийский'],
            'Яшнобод' => ['Яшнабадский', 'Юнусабадский'],
            'Миробод' => ['Мирабадский'],
            'Янгихаёт' => ['Алмазарский'], // Falls under Almazarsky
            'Юнусобод' => ['Юнусабадский'],
            'Чилонзор' => ['Чиланзарский'],
            'Учтепа' => ['Учтепинский'],
            'Бектемир' => ['Бектемирский'],
        ];
        
        foreach ($allDistricts as $district) {
            // Get contracts for this district
            $contracts = Contract::whereHas('object', function($q) use ($district) {
                $q->where('district_id', $district->id);
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
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'payment_percentage' => $totalAmount > 0 ? ($paidAmount / $totalAmount) * 100 : 0,
            ]);
        }
        
        // Sort by contracts count descending, then by total amount
        return $districtStats->sortByDesc('contracts_count')->values();
    }

    private function getDebtorsCount()
    {
        $contractsWithDebt = Contract::whereHas('paymentSchedules', function($q) {
            $q->where('is_active', true);
        })
        ->where('is_active', true)
        ->get()
        ->filter(function($contract) {
            $totalScheduled = $contract->paymentSchedules->sum('quarter_amount');
            $totalPaid = $contract->actualPayments->sum('amount');
            return $totalScheduled > $totalPaid;
        });

        return $contractsWithDebt->count();
    }

    public function getChartDataAjax(Request $request)
    {
        $period = $request->get('period', 'month');
        $chartData = $this->getChartData($period);
        
        return response()->json($chartData);
    }

    public function export()
    {
        $contracts = Contract::with(['subject', 'object.district', 'status'])
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
        $contracts = Contract::whereHas('object', function($q) use ($district) {
            $q->where('district_id', $district->id);
        })
        ->with(['subject', 'status'])
        ->where('is_active', true)
        ->get();

        $stats = [
            'total_contracts' => $contracts->count(),
            'total_amount' => $contracts->sum('total_amount'),
            'total_paid' => ActualPayment::whereIn('contract_id', $contracts->pluck('id'))->sum('amount'),
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
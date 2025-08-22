<?php

// app/Http/Controllers/DashboardController.php - UPDATED VERSION
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\ActualPayment;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
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

        // Oylik statistika - FIXED VERSION
        $monthlyStats = ActualPayment::selectRaw('
            YEAR(payment_date) as year,
            MONTH(payment_date) as month,
            SUM(amount) as total_amount,
            COUNT(*) as payments_count
        ')
        ->groupByRaw('YEAR(payment_date), MONTH(payment_date)')  // RAW GROUP BY ishlatamiz
        ->orderByRaw('YEAR(payment_date) DESC, MONTH(payment_date) DESC')
        ->limit(12)
        ->get();

        // Tumanlar bo'yicha statistika - FIXED VERSION
        $districtStats = DB::table('contracts as c')
            ->join('objects as o', 'c.object_id', '=', 'o.id')
            ->join('districts as d', 'o.district_id', '=', 'd.id')
            ->leftJoin('actual_payments as ap', 'c.id', '=', 'ap.contract_id')
            ->selectRaw('
                d.id as district_id,
                d.name_ru as district_name,
                COUNT(DISTINCT c.id) as contracts_count,
                SUM(DISTINCT c.total_amount) as total_amount,
                COALESCE(SUM(ap.amount), 0) as paid_amount
            ')
            ->where('c.is_active', true)
            ->groupBy('d.id', 'd.name_ru')  // Explicit GROUP BY
            ->get();

        return view('dashboard', compact('stats', 'monthlyStats', 'districtStats'));
    }

    private function getDebtorsCount()
    {
        // Simplified query to avoid GROUP BY issues
        $contractsWithSchedules = Contract::whereHas('paymentSchedules', function($q) {
            $q->where('is_active', true);
        })->where('is_active', true)->get();

        $debtorsCount = 0;
        foreach ($contractsWithSchedules as $contract) {
            $hasDebt = false;
            foreach ($contract->paymentSchedules()->where('is_active', true)->get() as $schedule) {
                $paidInQuarter = $contract->actualPayments()
                    ->where('year', $schedule->year)
                    ->where('quarter', $schedule->quarter)
                    ->sum('amount');

                if ($schedule->quarter_amount > $paidInQuarter) {
                    $hasDebt = true;
                    break;
                }
            }
            if ($hasDebt) {
                $debtorsCount++;
            }
        }

        return $debtorsCount;
    }
}

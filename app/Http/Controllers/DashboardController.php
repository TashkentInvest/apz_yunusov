<?php

// app/Http/Controllers/DashboardController.php
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

        // Oylik statistika
        $monthlyStats = ActualPayment::selectRaw('
            YEAR(payment_date) as year,
            MONTH(payment_date) as month,
            SUM(amount) as total_amount,
            COUNT(*) as payments_count
        ')
        ->groupBy('year', 'month')
        ->orderBy('year', 'desc')
        ->orderBy('month', 'desc')
        ->limit(12)
        ->get();

        // Tumanlar bo'yicha statistika
        $districtStats = DB::table('contracts as c')
            ->join('objects as o', 'c.object_id', '=', 'o.id')
            ->join('districts as d', 'o.district_id', '=', 'd.id')
            ->selectRaw('
                d.name_ru as district_name,
                COUNT(c.id) as contracts_count,
                SUM(c.total_amount) as total_amount,
                COALESCE(SUM(ap.paid_amount), 0) as paid_amount
            ')
            ->leftJoinSub(
                ActualPayment::selectRaw('contract_id, SUM(amount) as paid_amount')
                    ->groupBy('contract_id'),
                'ap',
                'c.id',
                '=',
                'ap.contract_id'
            )
            ->where('c.is_active', true)
            ->groupBy('d.id', 'd.name_ru')
            ->get();

        return view('dashboard', compact('stats', 'monthlyStats', 'districtStats'));
    }

    private function getDebtorsCount()
    {
        return Contract::whereHas('paymentSchedules', function($q) {
            $q->where('is_active', true)
              ->where('quarter_amount', '>',
                  DB::raw('(SELECT COALESCE(SUM(amount), 0) FROM actual_payments WHERE contract_id = contracts.id AND year = payment_schedules.year AND quarter = payment_schedules.quarter)')
              );
        })->count();
    }
}

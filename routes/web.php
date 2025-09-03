<?php

use App\Http\Controllers\KengashHulosasiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\DocumentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartDataAjax'])->name('dashboard.chart-data');
Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
Route::get('/dashboard/district/{district}', [DashboardController::class, 'districtDetails'])->name('dashboard.district');
// Contracts Management
Route::prefix('contracts')->name('contracts.')->group(function () {

 Route::get('/{contract}/quarterly-breakdown', [ContractController::class, 'getQuarterlyBreakdown'])->name('quarterly_breakdown');
    Route::post('/{contract}/create-quarterly-schedule', [ContractController::class, 'createQuarterlySchedule'])->name('create_quarterly_schedule');


    // Main contract routes
    Route::get('/', [ContractController::class, 'index'])->name('index');
    Route::get('/create', [ContractController::class, 'create'])->name('create');
    Route::post('/store', [ContractController::class, 'store'])->name('store');
    Route::get('/{contract}', [ContractController::class, 'show'])->name('show');
    Route::get('/{contract}/edit', [ContractController::class, 'edit'])->name('edit');
    Route::put('/{contract}', [ContractController::class, 'update'])->name('contracts.update');

    Route::delete('/{contract}', [ContractController::class, 'destroy'])->name('destroy');

    // Zone detection
    Route::post('/detect-zone', [ContractController::class, 'getZoneByCoordinates'])->name('detect-zone');

    // Contract amendments
    Route::post('/{contract}/amendments', [ContractController::class, 'createAmendment'])->name('amendments.store');

    // AJAX routes for creating subjects and objects
    Route::post('/create-subject', [ContractController::class, 'createSubject'])->name('createSubject');
    Route::post('/create-object', [ContractController::class, 'createObject'])->name('createObject');
    Route::get('/objects-by-subject/{subject}', [ContractController::class, 'getObjectsBySubject']);
    Route::post('/calculate-coefficients', [ContractController::class, 'calculateCoefficients']);
    Route::post('/validate-volumes', [ContractController::class, 'validateObjectVolumes']);

    // Payment Management Routes
    Route::prefix('{contract}')->group(function () {
        // Payment update page
        Route::get('/payment-update', [ContractController::class, 'payment_update'])->name('payment_update');

        // Payment summary and analytics
        Route::get('/payment-summary', [ContractController::class, 'getContractPaymentSummary'])->name('payment_summary');
        Route::get('/quarterly-breakdown', [ContractController::class, 'getQuarterlyBreakdown'])->name('quarterly_breakdown');
        Route::get('/next-payment-due', [ContractController::class, 'getNextPaymentDue'])->name('next_payment_due');

        // Payment schedule management
        Route::post('/create-quarterly-schedule', [ContractController::class, 'createQuarterlySchedule'])->name('create_quarterly_schedule');

        Route::post('/store-plan-payment', [ContractController::class, 'storePlanPayment'])->name('store_plan_payment');
        Route::put('/plan-payment/{id}', [ContractController::class, 'updatePlanPayment'])->name('update_plan_payment');
        Route::delete('/plan-payment/{id}', [ContractController::class, 'deletePlanPayment'])->name('delete_plan_payment');

        // Actual payments management
        Route::post('/store-fact-payment', [ContractController::class, 'storeFactPayment'])->name('store_fact_payment');
        Route::put('/fact-payment/{id}', [ContractController::class, 'editPayment'])->name('edit_fact_payment');
        Route::delete('/fact-payment/{id}', [ContractController::class, 'deleteFactPayment'])->name('delete_fact_payment');

        // Payment validation and calculations
        Route::post('/validate-payment-distribution', [ContractController::class, 'validatePaymentDistribution'])->name('validate_payment_distribution');
        Route::post('/calculate-remaining-amount', [ContractController::class, 'calculateRemainingAmount'])->name('calculate_remaining_amount');

        // Payment reports and exports
        Route::get('/payment-report', [ContractController::class, 'generatePaymentReport'])->name('payment_report');
        Route::get('/export-payment-schedule', [ContractController::class, 'exportPaymentSchedule'])->name('export_payment_schedule');
        Route::get('/export-actual-payments', [ContractController::class, 'exportActualPayments'])->name('export_actual_payments');
    });

    // Bulk payment operations (outside of specific contract context)
    Route::prefix('payments')->name('payments.')->group(function () {

         Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::put('/schedule/{contract}', [PaymentController::class, 'updateSchedule'])->name('schedule.update');


        Route::post('/bulk-create', [ContractController::class, 'bulkCreatePayments'])->name('bulk_create');
        Route::post('/bulk-update', [ContractController::class, 'bulkUpdatePayments'])->name('bulk_update');
        Route::delete('/bulk-delete', [ContractController::class, 'bulkDeletePayments'])->name('bulk_delete');

        // Payment analytics across all contracts
        Route::get('/analytics', [ContractController::class, 'getPaymentAnalytics'])->name('analytics');
        Route::get('/overdue-payments', [ContractController::class, 'getOverduePayments'])->name('overdue');
        Route::get('/upcoming-payments', [ContractController::class, 'getUpcomingPayments'])->name('upcoming');
        Route::get('/payment-statistics', [ContractController::class, 'getPaymentStatistics'])->name('statistics');
    });

    // Additional utility routes
    Route::get('/quarter-from-date/{date}', [ContractController::class, 'getQuarterFromDate'])->name('quarter_from_date');
    Route::get('/validate-payment-date', [ContractController::class, 'validatePaymentDate'])->name('validate_payment_date');
});

// Route::prefix('contracts')->group(function () {
//     Route::get('/', [ContractController::class, 'index'])->name('contracts.index');
//     Route::get('/create', [ContractController::class, 'create'])->name('contracts.create');
//     Route::post('/store', [ContractController::class, 'store'])->name('contracts.store');
//     Route::get('/{contract}', [ContractController::class, 'show'])->name('contracts.show');
//     Route::get('/{contract}/edit', [ContractController::class, 'edit'])->name('contracts.edit');
//     Route::put('/{contract}', [ContractController::class, 'update'])->name('contracts.update');
//     Route::delete('/{contract}', [ContractController::class, 'destroy'])->name('contracts.destroy');

//     // Zone detection endpoint
//     Route::post('/detect-zone', [ContractController::class, 'getZoneByCoordinates'])->name('detect-zone');

//     // Additional AJAX endpoints
//     Route::get('/objects-by-subject/{subject}', [ContractController::class, 'getObjectsBySubject']);
//     Route::post('/calculate-coefficients', [ContractController::class, 'calculateCoefficients']);
//     Route::post('/validate-volumes', [ContractController::class, 'validateObjectVolumes']);
// });



// Payments Management


// Objects Management
Route::prefix('objects')->group(function () {
    Route::get('/', [ObjectController::class, 'index'])->name('objects.index');
    Route::get('/create', [ObjectController::class, 'create'])->name('objects.create');
    Route::post('/store', [ObjectController::class, 'store'])->name('objects.store');
    Route::get('/{object}', [ObjectController::class, 'show'])->name('objects.show');
    Route::get('/{object}/edit', [ObjectController::class, 'edit'])->name('objects.edit');
    Route::put('/{object}', [ObjectController::class, 'update'])->name('objects.update');
    Route::delete('/{object}', [ObjectController::class, 'destroy'])->name('objects.destroy');
});

// Subjects (Customers) Management
Route::prefix('subjects')->group(function () {
    Route::get('/', [SubjectController::class, 'index'])->name('subjects.index');
    Route::get('/create', [SubjectController::class, 'create'])->name('subjects.create');
    Route::post('/store', [SubjectController::class, 'store'])->name('subjects.store');
    Route::get('/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
    Route::get('/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
    Route::put('/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
});



// Documents Generation
Route::prefix('documents')->name('documents.')->group(function () {
    Route::get('/contracts/{contract}/demand-notice', [DocumentController::class, 'demandNotice'])->name('demand-notice');
    Route::get('/contracts/{contract}/amendments/{amendment}', [DocumentController::class, 'amendment'])->name('amendment');
    Route::get('/contracts/{contract}/cancellation', [DocumentController::class, 'cancellation'])->name('cancellation');
});

// API Routes for AJAX requests
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/contracts/{contract}/penalties', function(\App\Models\Contract $contract) {
        $contractController = new ContractController();
        $penalties = $contractController->calculatePenalties($contract);
        return response()->json($penalties);
    })->name('contracts.penalties');

    Route::get('/subjects/search', function(\Illuminate\Http\Request $request) {
        $query = $request->get('q');
        $subjects = \App\Models\Subject::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('company_name', 'like', "%{$query}%")
                  ->orWhere('inn', 'like', "%{$query}%")
                  ->orWhere('pinfl', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function($subject) {
                return [
                    'id' => $subject->id,
                    'text' => $subject->display_name . ' (' . $subject->identifier . ')'
                ];
            });

    Route::get('/objects/search', [App\Http\Controllers\ObjectController::class, 'search'])->name('objects.search');

    Route::get('/statistics/monthly', function() {
        $monthlyStats = \App\Models\ActualPayment::selectRaw('
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

        return response()->json($monthlyStats);
    })->name('statistics.monthly');

    Route::get('/statistics/districts', function() {
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
                \App\Models\ActualPayment::selectRaw('contract_id, SUM(amount) as paid_amount')
                    ->groupBy('contract_id'),
                'ap',
                'c.id',
                '=',
                'ap.contract_id'
            )
            ->where('c.is_active', true)
            ->groupBy('d.id', 'd.name_ru')
            ->get();

        return response()->json($districtStats);
    })->name('statistics.districts');
});

// Export Routes
Route::prefix('export')->name('export.')->group(function () {
    Route::get('/contracts', function(\Illuminate\Http\Request $request) {
        // Export contracts to Excel
        $query = \App\Models\Contract::with(['subject', 'object.district', 'status']);

        if ($request->contract_number) {
            $query->where('contract_number', 'like', '%' . $request->contract_number . '%');
        }
        if ($request->district_id) {
            $query->whereHas('object', function($q) use ($request) {
                $q->where('district_id', $request->district_id);
            });
        }
        if ($request->status_id) {
            $query->where('status_id', $request->status_id);
        }

        $contracts = $query->get();

        $filename = 'contracts_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($contracts) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Headers
            fputcsv($file, [
                'Номер договора',
                'Заказчик',
                'ИНН/ПИНФЛ',
                'Район',
                'Адрес',
                'Сумма договора',
                'Оплачено',
                'Остаток',
                'Статус',
                'Дата договора',
                'Объем м³'
            ]);

            foreach ($contracts as $contract) {
                fputcsv($file, [
                    $contract->contract_number,
                    $contract->subject->display_name,
                    $contract->subject->identifier,
                    $contract->object->district->name_ru ?? '',
                    $contract->object->address ?? '',
                    $contract->total_amount,
                    $contract->total_paid,
                    $contract->remaining_debt,
                    $contract->status->name_ru,
                    $contract->contract_date->format('d.m.Y'),
                    $contract->contract_volume
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    })->name('contracts');



    Route::get('/payments', function(\Illuminate\Http\Request $request) {
        // Export payments to Excel
        $query = \App\Models\ActualPayment::with(['contract.subject', 'contract.object.district']);

        if ($request->year) {
            $query->where('year', $request->year);
        }
        if ($request->quarter) {
            $query->where('quarter', $request->quarter);
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        $filename = 'payments_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Headers
            fputcsv($file, [
                'Номер платежа',
                'Дата платежа',
                'Договор',
                'Заказчик',
                'Сумма',
                'Год',
                'Квартал',
                'Примечание'
            ]);

            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->payment_number,
                    $payment->payment_date->format('d.m.Y'),
                    $payment->contract->contract_number,
                    $payment->contract->subject->display_name,
                    $payment->amount,
                    $payment->year,
                    $payment->quarter,
                    $payment->notes
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    })->name('payments');

    Route::get('/debtors', function() {
        // Export debtors list
        $debtors = \App\Models\Contract::with(['subject', 'object.district', 'status'])
            ->whereHas('paymentSchedules', function($q) {
                $q->where('is_active', true)
                  ->whereRaw('quarter_amount > (SELECT COALESCE(SUM(amount), 0) FROM actual_payments WHERE contract_id = contracts.id AND year = payment_schedules.year AND quarter = payment_schedules.quarter)');
            })
            ->where('is_active', true)
            ->get();

        $filename = 'debtors_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($debtors) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Headers
            fputcsv($file, [
                'Номер договора',
                'Заказчик',
                'ИНН/ПИНФЛ',
                'Телефон',
                'Район',
                'Сумма договора',
                'Оплачено',
                'Задолженность',
                'Процент оплаты'
            ]);

            foreach ($debtors as $contract) {
                fputcsv($file, [
                    $contract->contract_number,
                    $contract->subject->display_name,
                    $contract->subject->identifier,
                    $contract->subject->phone ?? '',
                    $contract->object->district->name_ru ?? '',
                    $contract->total_amount,
                    $contract->total_paid,
                    $contract->remaining_debt,
                    $contract->payment_percent . '%'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    })->name('debtors');
});

// Health check route
Route::get('/health', function() {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});

// Fallback route for 404
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
});

// Route::middleware(['auth'])->group(function () {
    // Kengash Hulosa routes
    Route::resource('kengash-hulosa', KengashHulosasiController::class)->parameters([
        'kengash-hulosa' => 'kengashHulosa'
    ]);

    // Additional routes
    Route::post('kengash-hulosa/import', [KengashHulosasiController::class, 'import'])
         ->name('kengash-hulosa.import');

    Route::get('kengash-hulosa-export', [KengashHulosasiController::class, 'export'])
         ->name('kengash-hulosa.export');

    Route::get('kengash-hulosa-svod', [KengashHulosasiController::class, 'svod'])
         ->name('kengash-hulosasi.svod');

    Route::delete('kengash-hulosa-file/{file}', [KengashHulosasiController::class, 'deleteFile'])
         ->name('kengash-hulosa.file.delete');

    Route::get('kengash-hulosa-file/{file}/download', [KengashHulosasiController::class, 'downloadFile'])
         ->name('kengash-hulosa.file.download');
// });


Route::get('/zona.kml', function () {
    $path = public_path('zona.kml');
    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path, [
        'Content-Type' => 'application/vnd.google-earth.kml+xml',
        'Content-Disposition' => 'inline; filename="zona.kml"'
    ]);
})->name('zona.kml');
// web.php

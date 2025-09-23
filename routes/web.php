<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KengashHulosasiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ObjectController;

Route::get('/', function () {
    return redirect('/login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {

    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
    Route::get('/dashboard/contracts/{status}', [DashboardController::class, 'contractsByStatus'])->name('dashboard.contracts.status');
    Route::get('/dashboard/district/{district}/contracts', [DashboardController::class, 'districtContracts'])->name('dashboard.district.contracts');

    // Contracts Management
    Route::prefix('contracts')->name('contracts.')->group(function () {

        // Basic CRUD - Place specific routes BEFORE resource routes
        Route::get('/debtors/list', [ContractController::class, 'debtors'])->name('debtors');
        Route::get('/create', [ContractController::class, 'create'])->name('create');
        Route::post('/store', [ContractController::class, 'store'])->name('store');

        // Utility routes (must come before dynamic parameters)
        Route::post('/create-subject', [ContractController::class, 'createSubject'])->name('createSubject');
        Route::post('/create-object', [ContractController::class, 'createObject'])->name('createObject');
        Route::get('/objects-by-subject/{subject}', [ContractController::class, 'getObjectsBySubject'])->name('objects_by_subject');
        Route::post('/calculate-coefficients', [ContractController::class, 'calculateCoefficients'])->name('calculate_coefficients');
        Route::post('/validate-volumes', [ContractController::class, 'validateObjectVolumes'])->name('validate_volumes');
        Route::post('/validate-payment-date-global', [ContractController::class, 'validatePaymentDate'])->name('global_validate_payment_date');
        Route::get('/quarter-from-date-global/{date}', [ContractController::class, 'getQuarterFromDate'])->name('quarter_from_date');

        // Contract specific routes
        Route::get('/{contract}', [ContractController::class, 'show'])->name('show')->whereNumber('contract');
        Route::get('/{contract}/edit', [ContractController::class, 'edit'])->name('edit')->whereNumber('contract');
        Route::put('/{contract}', [ContractController::class, 'update'])->name('update')->whereNumber('contract');
        Route::delete('/{contract}', [ContractController::class, 'destroy'])->name('destroy')->whereNumber('contract');
        Route::patch('/{contract}/update-status', [ContractController::class, 'updateStatus'])->name('update-status')->whereNumber('contract');

        // Payment Management
        Route::get('/{contract}/payment-update', [ContractController::class, 'payment_update'])->name('payment_update')->whereNumber('contract');
        Route::get('/{contract}/create-schedule', [ContractController::class, 'createSchedule'])->name('create-schedule')->whereNumber('contract');
        Route::post('/{contract}/store-schedule', [ContractController::class, 'storeSchedule'])->name('store-schedule')->whereNumber('contract');
        Route::get('/{contract}/add-payment', [ContractController::class, 'addPayment'])->name('add-payment')->whereNumber('contract');
        Route::post('/{contract}/store-payment', [ContractController::class, 'storePayment'])->name('store-payment')->whereNumber('contract');
        Route::get('/{contract}/add-quarter-payment/{year}/{quarter}', [ContractController::class, 'addQuarterPayment'])->name('add-quarter-payment')->whereNumber('contract');
        Route::get('/{contract}/quarter-details/{year}/{quarter}', [ContractController::class, 'quarterDetails'])->name('quarter-details')->whereNumber('contract');

        // Contract Amendments - FIXED STRUCTURE
        Route::get('/{contract}/amendments/create', [ContractController::class, 'createAmendment'])->name('amendments.create')->whereNumber('contract');
        Route::post('/{contract}/amendments/store', [ContractController::class, 'storeAmendment'])->name('amendments.store')->whereNumber('contract');
        Route::get('/{contract}/amendments/{amendment}', [ContractController::class, 'showAmendment'])->name('amendments.show')->whereNumber(['contract', 'amendment']);
        Route::post('/{contract}/amendments/{amendment}/approve', [ContractController::class, 'approveAmendment'])->name('amendments.approve')->whereNumber(['contract', 'amendment']);
        Route::delete('/{contract}/amendments/{amendment}', [ContractController::class, 'deleteAmendment'])->name('amendments.delete')->whereNumber(['contract', 'amendment']);
        Route::post('/{contract}/amendments/{amendment}/create-schedule', [ContractController::class, 'createAmendmentSchedule'])->name('amendments.create-schedule')->whereNumber(['contract', 'amendment']);

        // Reports and Exports
        Route::get('/{contract}/export-report', [ContractController::class, 'exportReport'])->name('export-report')->whereNumber('contract');
        Route::get('/{contract}/generate-payment-report', [ContractController::class, 'generatePaymentReport'])->name('generate-payment-report')->whereNumber('contract');

        // API endpoints
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/{contract}/quarterly-breakdown', [ContractController::class, 'getQuarterlyBreakdown'])->name('quarterly-breakdown')->whereNumber('contract');
            Route::get('/{contract}/payment-history', [ContractController::class, 'getPaymentHistory'])->name('payment-history')->whereNumber('contract');
            Route::get('/{contract}/summary', [ContractController::class, 'getContractPaymentSummary'])->name('summary')->whereNumber('contract');
            Route::get('/{contract}/amendments', [ContractController::class, 'getAmendments'])->name('amendments')->whereNumber('contract');
            Route::get('/{contract}/initial-payments', [ContractController::class, 'getInitialPayments'])->name('initial-payments')->whereNumber('contract');
            Route::post('/calculate-breakdown', [ContractController::class, 'calculateBreakdown'])->name('calculate-breakdown');
            Route::post('/validate-payment-date', [ContractController::class, 'validatePaymentDate'])->name('validate-payment-date');
            Route::get('/quarter-from-date/{date}', [ContractController::class, 'getQuarterFromDate'])->name('quarter-from-date');
        });

        // Legacy support routes
        Route::prefix('legacy')->name('legacy.')->group(function () {
            Route::post('/{contract}/create-quarterly-schedule', [ContractController::class, 'createQuarterlySchedule'])->name('create_quarterly_schedule')->whereNumber('contract');
            Route::post('/{contract}/store-fact-payment', [ContractController::class, 'storeFactPayment'])->name('store_fact_payment')->whereNumber('contract');
            Route::put('/fact-payment/{payment}', [ContractController::class, 'editPayment'])->name('edit_fact_payment')->whereNumber('payment');
            Route::delete('/fact-payment/{payment}', [ContractController::class, 'deleteFactPayment'])->name('delete_fact_payment')->whereNumber('payment');
        });

        // Index route must be last
        Route::get('/', [ContractController::class, 'index'])->name('index');
    });

    // Payment operations (global)
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::post('/store', [ContractController::class, 'storePayment'])->name('store');
        Route::post('/{paymentId}/update', [ContractController::class, 'updatePayment'])->name('update')->whereNumber('paymentId');
        Route::delete('/{paymentId}/delete', [ContractController::class, 'deletePayment'])->name('delete')->whereNumber('paymentId');
        Route::get('/{paymentId}/details', [ContractController::class, 'getPaymentDetails'])->name('details')->whereNumber('paymentId');
        Route::get('/analytics', [ContractController::class, 'getPaymentAnalytics'])->name('analytics');
        Route::get('/overdue-payments', [ContractController::class, 'getOverduePayments'])->name('overdue');
        Route::get('/upcoming-payments', [ContractController::class, 'getUpcomingPayments'])->name('upcoming');
        Route::get('/payment-statistics', [ContractController::class, 'getPaymentStatistics'])->name('statistics');
        Route::post('/bulk-create', [ContractController::class, 'bulkCreatePayments'])->name('bulk_create');
        Route::post('/bulk-update', [ContractController::class, 'bulkUpdatePayments'])->name('bulk_update');
        Route::delete('/bulk-delete', [ContractController::class, 'bulkDeletePayments'])->name('bulk_delete');
    });

    // Objects Management
    Route::prefix('objects')->name('objects.')->group(function () {
        Route::get('/create', [ObjectController::class, 'create'])->name('create');
        Route::post('/store', [ObjectController::class, 'store'])->name('store');
        Route::get('/{object}', [ObjectController::class, 'show'])->name('show')->whereNumber('object');
        Route::get('/{object}/edit', [ObjectController::class, 'edit'])->name('edit')->whereNumber('object');
        Route::put('/{object}', [ObjectController::class, 'update'])->name('update')->whereNumber('object');
        Route::delete('/{object}', [ObjectController::class, 'destroy'])->name('destroy')->whereNumber('object');
        Route::get('/', [ObjectController::class, 'index'])->name('index');
    });

    // Subjects Management
    Route::prefix('subjects')->name('subjects.')->group(function () {
        Route::get('/create', [SubjectController::class, 'create'])->name('create');
        Route::post('/store', [SubjectController::class, 'store'])->name('store');
        Route::get('/{subject}', [SubjectController::class, 'show'])->name('show')->whereNumber('subject');
        Route::get('/{subject}/edit', [SubjectController::class, 'edit'])->name('edit')->whereNumber('subject');
        Route::put('/{subject}', [SubjectController::class, 'update'])->name('update')->whereNumber('subject');
        Route::delete('/{subject}', [SubjectController::class, 'destroy'])->name('destroy')->whereNumber('subject');
        Route::get('/', [SubjectController::class, 'index'])->name('index');
    });

    // Documents Generation
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/contracts/{contract}/demand-notice', [DocumentController::class, 'demandNotice'])->name('demand-notice')->whereNumber('contract');
        Route::get('/contracts/{contract}/amendments/{amendment}', [DocumentController::class, 'amendment'])->name('amendment')->whereNumber(['contract', 'amendment']);
        Route::get('/contracts/{contract}/cancellation', [DocumentController::class, 'cancellation'])->name('cancellation')->whereNumber('contract');
    });

    // Global API Routes
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/contracts/{contract}/penalties', function (\App\Models\Contract $contract) {
            $contractController = app(ContractController::class);
            $penalties = $contractController->calculatePenalties($contract);
            return response()->json($penalties);
        })->name('contracts.penalties')->whereNumber('contract');

        Route::get('/subjects/search', function (\Illuminate\Http\Request $request) {
            $query = $request->get('q');
            $subjects = \App\Models\Subject::where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('company_name', 'like', "%{$query}%")
                        ->orWhere('inn', 'like', "%{$query}%")
                        ->orWhere('pinfl', 'like', "%{$query}%");
                })
                ->limit(10)
                ->get()
                ->map(function ($subject) {
                    return [
                        'id' => $subject->id,
                        'text' => $subject->display_name . ' (' . $subject->identifier . ')'
                    ];
                });
            return response()->json($subjects);
        })->name('subjects.search');

        Route::get('/objects/search', [ObjectController::class, 'search'])->name('objects.search');

        Route::get('/statistics/monthly', function () {
            $monthlyStats = \App\Models\ActualPayment::selectRaw('
                YEAR(payment_date) as year,
                MONTH(payment_date) as month,
                SUM(amount) as total_amount,
                COUNT(*) as payments_count,
                SUM(CASE WHEN is_initial_payment = 1 THEN amount ELSE 0 END) as initial_payments_amount,
                SUM(CASE WHEN is_initial_payment = 0 THEN amount ELSE 0 END) as quarterly_payments_amount,
                COUNT(CASE WHEN is_initial_payment = 1 THEN 1 END) as initial_payments_count,
                COUNT(CASE WHEN is_initial_payment = 0 THEN 1 END) as quarterly_payments_count
            ')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get();
            return response()->json($monthlyStats);
        })->name('statistics.monthly');

        Route::get('/statistics/districts', function () {
            $districtStats = DB::table('contracts as c')
                ->join('objects as o', 'c.object_id', '=', 'o.id')
                ->join('districts as d', 'o.district_id', '=', 'd.id')
                ->selectRaw('
                    d.name_ru as district_name,
                    COUNT(c.id) as contracts_count,
                    SUM(c.total_amount) as total_amount,
                    COALESCE(SUM(ap.paid_amount), 0) as paid_amount,
                    COALESCE(SUM(ap.initial_paid_amount), 0) as initial_paid_amount,
                    COALESCE(SUM(ap.quarterly_paid_amount), 0) as quarterly_paid_amount
                ')
                ->leftJoinSub(
                    \App\Models\ActualPayment::selectRaw('
                        contract_id,
                        SUM(amount) as paid_amount,
                        SUM(CASE WHEN is_initial_payment = 1 THEN amount ELSE 0 END) as initial_paid_amount,
                        SUM(CASE WHEN is_initial_payment = 0 THEN amount ELSE 0 END) as quarterly_paid_amount
                    ')
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

        Route::get('/statistics/payment-categories', function () {
            $categoryStats = \App\Models\ActualPayment::selectRaw('
                payment_category,
                is_initial_payment,
                COUNT(*) as count,
                SUM(amount) as total_amount,
                AVG(amount) as average_amount
            ')
                ->groupBy('payment_category', 'is_initial_payment')
                ->get()
                ->map(function ($stat) {
                    return [
                        'category' => $stat->payment_category,
                        'is_initial' => $stat->is_initial_payment,
                        'type_name' => $stat->is_initial_payment ? 'Boshlang\'ich to\'lov' : 'Chorak to\'lovi',
                        'count' => $stat->count,
                        'total_amount' => $stat->total_amount,
                        'average_amount' => $stat->average_amount,
                        'total_formatted' => number_format($stat->total_amount, 0, '.', ' ') . ' so\'m'
                    ];
                });
            return response()->json($categoryStats);
        })->name('statistics.payment-categories');
    });

    // Export Routes
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/contracts', function (\Illuminate\Http\Request $request) {
            // Your existing export logic
            $query = \App\Models\Contract::with(['subject', 'object.district', 'status']);
            // ... rest of the code
            return response()->stream($callback ?? function() {}, 200, $headers ?? []);
        })->name('contracts');

        Route::get('/payments', function (\Illuminate\Http\Request $request) {
            // Your existing export logic
            return response()->stream($callback ?? function() {}, 200, $headers ?? []);
        })->name('payments');

        Route::get('/debtors', function (\Illuminate\Http\Request $request) {
            // Your existing export logic
            return response()->stream($callback ?? function() {}, 200, $headers ?? []);
        })->name('debtors');
    });

    // Kengash Hulosa routes
    Route::prefix('kengash-hulosa')->name('kengash-hulosa.')->group(function () {
        Route::get('/', [KengashHulosasiController::class, 'index'])->name('index');
        Route::get('/create', [KengashHulosasiController::class, 'create'])->name('create');
        Route::post('/', [KengashHulosasiController::class, 'store'])->name('store');
        Route::get('/{kengashHulosa}', [KengashHulosasiController::class, 'show'])->name('show');
        Route::get('/{kengashHulosa}/edit', [KengashHulosasiController::class, 'edit'])->name('edit');
        Route::put('/{kengashHulosa}', [KengashHulosasiController::class, 'update'])->name('update');
        Route::delete('/{kengashHulosa}', [KengashHulosasiController::class, 'destroy'])->name('destroy');
        Route::post('/import', [KengashHulosasiController::class, 'import'])->name('import');
        Route::get('/export-data', [KengashHulosasiController::class, 'export'])->name('export');
        Route::get('/svod', [KengashHulosasiController::class, 'svod'])->name('svod');
    });

    Route::delete('kengash-hulosa-file/{file}', [KengashHulosasiController::class, 'deleteFile'])->name('kengash-hulosa.file.delete');
    Route::get('kengash-hulosa-file/{file}/download', [KengashHulosasiController::class, 'downloadFile'])->name('kengash-hulosa.file.download');

    // Zone KML file serving
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
});

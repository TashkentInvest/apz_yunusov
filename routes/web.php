<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KengashHulosasiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\PaymentController;
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

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartDataAjax'])->name('dashboard.chart-data');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
    Route::get('/dashboard/district/{district}', [DashboardController::class, 'districtDetails'])->name('dashboard.district');

    // Contracts Management - Clean and organized
    Route::prefix('contracts')->name('contracts.')->group(function () {

        // Basic CRUD
        Route::get('/', [ContractController::class, 'index'])->name('index');
        Route::get('/create', [ContractController::class, 'create'])->name('create');
        Route::post('/store', [ContractController::class, 'store'])->name('store');
        Route::get('/{contract}', [ContractController::class, 'show'])->name('show');
        Route::get('/{contract}/edit', [ContractController::class, 'edit'])->name('edit');
        Route::put('/{contract}', [ContractController::class, 'update'])->name('update');
        Route::delete('/{contract}', [ContractController::class, 'destroy'])->name('destroy');

        // Payment Management - Main page
        Route::get('/{contract}/payment-update', [ContractController::class, 'payment_update'])->name('payment_update');

        // Payment Schedule Management
        Route::get('/{contract}/create-schedule', [ContractController::class, 'createSchedule'])->name('create-schedule');
        Route::post('/{contract}/store-schedule', [ContractController::class, 'storeSchedule'])->name('store-schedule');

        // Payment Management
        Route::get('/{contract}/add-payment', [ContractController::class, 'addPayment'])->name('add-payment');
        Route::post('/{contract}/store-payment', [ContractController::class, 'storePayment'])->name('store-payment');

        // Quarter-specific operations
        Route::get('/{contract}/add-quarter-payment/{year}/{quarter}', [ContractController::class, 'addQuarterPayment'])->name('add-quarter-payment');
        Route::get('/{contract}/quarter-details/{year}/{quarter}', [ContractController::class, 'quarterDetails'])->name('quarter-details');

        // Contract Amendments (Qo'shimcha kelishuvlar)
        Route::prefix('{contract}/amendments')->name('amendments.')->group(function () {
            Route::get('/create', [ContractController::class, 'createAmendment'])->name('create');
            Route::post('/store', [ContractController::class, 'storeAmendment'])->name('store');
            Route::get('/{amendment}', [ContractController::class, 'showAmendment'])->name('show');
            Route::post('/{amendment}/approve', [ContractController::class, 'approveAmendment'])->name('approve');
            Route::delete('/{amendment}', [ContractController::class, 'deleteAmendment'])->name('delete');
            Route::post('/{amendment}/create-schedule', [ContractController::class, 'createAmendmentSchedule'])->name('create-schedule');
        });

        // Payment operations with proper naming
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::post('/store', [ContractController::class, 'storePayment'])->name('store');

            // To'lov CRUD amaliyotlari
            Route::post('/{paymentId}/update', [ContractController::class, 'updatePayment'])->name('update');
            Route::delete('/{paymentId}/delete', [ContractController::class, 'deletePayment'])->name('delete');
            Route::get('/{paymentId}/details', [ContractController::class, 'getPaymentDetails'])->name('details');

            Route::get('/analytics', [ContractController::class, 'getPaymentAnalytics'])->name('analytics');
            Route::get('/overdue-payments', [ContractController::class, 'getOverduePayments'])->name('overdue');
            Route::get('/upcoming-payments', [ContractController::class, 'getUpcomingPayments'])->name('upcoming');
            Route::get('/payment-statistics', [ContractController::class, 'getPaymentStatistics'])->name('statistics');
            Route::post('/bulk-create', [ContractController::class, 'bulkCreatePayments'])->name('bulk_create');
            Route::post('/bulk-update', [ContractController::class, 'bulkUpdatePayments'])->name('bulk_update');
            Route::delete('/bulk-delete', [ContractController::class, 'bulkDeletePayments'])->name('bulk_delete');
        });

        // API endpoints (for AJAX calls)
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/{contract}/quarterly-breakdown', [ContractController::class, 'getQuarterlyBreakdown'])->name('quarterly-breakdown');
            Route::get('/{contract}/payment-history', [ContractController::class, 'getPaymentHistory'])->name('payment-history');
            Route::get('/{contract}/summary', [ContractController::class, 'getContractPaymentSummary'])->name('summary');
            Route::get('/{contract}/amendments', [ContractController::class, 'getAmendments'])->name('amendments');
            Route::post('/calculate-breakdown', [ContractController::class, 'calculateBreakdown'])->name('calculate-breakdown');
            Route::post('/validate-payment-date', [ContractController::class, 'validatePaymentDate'])->name('validate-payment-date');
            Route::get('/quarter-from-date/{date}', [ContractController::class, 'getQuarterFromDate'])->name('quarter-from-date');
        });

        // Reports and Exports
        Route::get('/{contract}/export-report', [ContractController::class, 'exportReport'])->name('export-report');
        Route::get('/{contract}/generate-payment-report', [ContractController::class, 'generatePaymentReport'])->name('generate-payment-report');

        // Legacy support routes (for backward compatibility)
        Route::prefix('legacy')->name('legacy.')->group(function () {
            Route::post('/{contract}/create-quarterly-schedule', [ContractController::class, 'createQuarterlySchedule'])->name('create_quarterly_schedule');
            Route::post('/{contract}/store-fact-payment', [ContractController::class, 'storeFactPayment'])->name('store_fact_payment');
            Route::put('/fact-payment/{payment}', [ContractController::class, 'editPayment'])->name('edit_fact_payment');
            Route::delete('/fact-payment/{payment}', [ContractController::class, 'deleteFactPayment'])->name('delete_fact_payment');
        });

        // Utility routes
        Route::post('/create-subject', [ContractController::class, 'createSubject'])->name('createSubject');
        Route::post('/create-object', [ContractController::class, 'createObject'])->name('createObject');
        Route::get('/objects-by-subject/{subject}', [ContractController::class, 'getObjectsBySubject'])->name('objects_by_subject');
        Route::post('/calculate-coefficients', [ContractController::class, 'calculateCoefficients'])->name('calculate_coefficients');
        Route::post('/validate-volumes', [ContractController::class, 'validateObjectVolumes'])->name('validate_volumes');

        // Global utility routes
        Route::post('/validate-payment-date-global', [ContractController::class, 'validatePaymentDate'])->name('global_validate_payment_date');
        Route::get('/quarter-from-date-global/{date}', [ContractController::class, 'getQuarterFromDate'])->name('quarter_from_date');
    });

    // Objects Management
    Route::prefix('objects')->name('objects.')->group(function () {
        Route::get('/', [ObjectController::class, 'index'])->name('index');
        Route::get('/create', [ObjectController::class, 'create'])->name('create');
        Route::post('/store', [ObjectController::class, 'store'])->name('store');
        Route::get('/{object}', [ObjectController::class, 'show'])->name('show');
        Route::get('/{object}/edit', [ObjectController::class, 'edit'])->name('edit');
        Route::put('/{object}', [ObjectController::class, 'update'])->name('update');
        Route::delete('/{object}', [ObjectController::class, 'destroy'])->name('destroy');
    });

    // Subjects (Customers) Management
    Route::prefix('subjects')->name('subjects.')->group(function () {
        Route::get('/', [SubjectController::class, 'index'])->name('index');
        Route::get('/create', [SubjectController::class, 'create'])->name('create');
        Route::post('/store', [SubjectController::class, 'store'])->name('store');
        Route::get('/{subject}', [SubjectController::class, 'show'])->name('show');
        Route::get('/{subject}/edit', [SubjectController::class, 'edit'])->name('edit');
        Route::put('/{subject}', [SubjectController::class, 'update'])->name('update');
        Route::delete('/{subject}', [SubjectController::class, 'destroy'])->name('destroy');
    });

    // Documents Generation
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/contracts/{contract}/demand-notice', [DocumentController::class, 'demandNotice'])->name('demand-notice');
        Route::get('/contracts/{contract}/amendments/{amendment}', [DocumentController::class, 'amendment'])->name('amendment');
        Route::get('/contracts/{contract}/cancellation', [DocumentController::class, 'cancellation'])->name('cancellation');
    });

    // API Routes for AJAX requests (Global)
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/contracts/{contract}/penalties', function (\App\Models\Contract $contract) {
            $contractController = app(ContractController::class);
            $penalties = $contractController->calculatePenalties($contract);
            return response()->json($penalties);
        })->name('contracts.penalties');

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

        Route::get('/objects/search', [App\Http\Controllers\ObjectController::class, 'search'])->name('objects.search');

        Route::get('/statistics/monthly', function () {
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

        Route::get('/statistics/districts', function () {
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
        Route::get('/contracts', function (\Illuminate\Http\Request $request) {
            $query = \App\Models\Contract::with(['subject', 'object.district', 'status']);

            if ($request->contract_number) {
                $query->where('contract_number', 'like', '%' . $request->contract_number . '%');
            }
            if ($request->district_id) {
                $query->whereHas('object', function ($q) use ($request) {
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

            $callback = function () use ($contracts) {
                $file = fopen('php://output', 'w');
                fwrite($file, "\xEF\xBB\xBF");

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
                        $contract->subject->display_name ?? '',
                        $contract->subject->identifier ?? '',
                        $contract->object->district->name_ru ?? '',
                        $contract->object->address ?? '',
                        $contract->total_amount,
                        $contract->getTotalPaidAmount(),
                        $contract->remaining_debt,
                        $contract->status->name_ru ?? '',
                        $contract->contract_date->format('d.m.Y'),
                        $contract->contract_volume
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        })->name('contracts');

        Route::get('/payments', function (\Illuminate\Http\Request $request) {
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

            $callback = function () use ($payments) {
                $file = fopen('php://output', 'w');
                fwrite($file, "\xEF\xBB\xBF");

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
                        $payment->contract->subject->display_name ?? '',
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
    });

    // Kengash Hulosa routes
    Route::resource('kengash-hulosa', KengashHulosasiController::class)->parameters([
        'kengash-hulosa' => 'kengashHulosa'
    ]);

    Route::post('kengash-hulosa/import', [KengashHulosasiController::class, 'import'])->name('kengash-hulosa.import');
    Route::get('kengash-hulosa-export', [KengashHulosasiController::class, 'export'])->name('kengash-hulosa.export');
    Route::get('kengash-hulosa-svod', [KengashHulosasiController::class, 'svod'])->name('kengash-hulosasi.svod');
    Route::delete('kengash-hulosa-file/{file}', [KengashHulosasiController::class, 'deleteFile'])->name('kengash-hulosa.file.delete');
    Route::get('kengash-hulosa-file/{file}/download', [KengashHulosasiController::class, 'downloadFile'])->name('kengash-hulosa.file.download');

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

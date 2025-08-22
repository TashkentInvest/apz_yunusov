<?php

// Export Routes qo'shish - routes/web.php ga qo'shish kerak

// Export Routes
Route::prefix('export')->name('export.')->group(function () {
    // Contracts export
    Route::get('/contracts', function(\Illuminate\Http\Request $request) {
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
            ], ';');

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
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    })->name('contracts');

    // Payments export
    Route::get('/payments', function(\Illuminate\Http\Request $request) {
        $query = \App\Models\ActualPayment::with(['contract.subject', 'contract.object.district']);

        if ($request->contract_number) {
            $query->whereHas('contract', function($q) use ($request) {
                $q->where('contract_number', 'like', '%' . $request->contract_number . '%');
            });
        }
        if ($request->year) {
            $query->where('year', $request->year);
        }
        if ($request->quarter) {
            $query->where('quarter', $request->quarter);
        }
        if ($request->period) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('payment_date', today());
                    break;
                case 'week':
                    $query->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('payment_date', now()->month)
                          ->whereYear('payment_date', now()->year);
                    break;
            }
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
            ], ';');

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
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    })->name('payments');

    // Debtors export
    Route::get('/debtors', function(\Illuminate\Http\Request $request) {
        $query = \App\Models\Contract::with(['subject', 'object.district', 'status'])
            ->whereHas('paymentSchedules', function($q) {
                $q->where('is_active', true)
                  ->whereRaw('quarter_amount > (SELECT COALESCE(SUM(amount), 0) FROM actual_payments WHERE contract_id = contracts.id AND year = payment_schedules.year AND quarter = payment_schedules.quarter)');
            })
            ->where('is_active', true);

        if ($request->contract_number) {
            $query->where('contract_number', 'like', '%' . $request->contract_number . '%');
        }
        if ($request->district_id) {
            $query->whereHas('object', function($q) use ($request) {
                $q->where('district_id', $request->district_id);
            });
        }
        if ($request->debt_from) {
            $debtFrom = floatval($request->debt_from);
            $query->whereRaw('(total_amount - (SELECT COALESCE(SUM(amount), 0) FROM actual_payments WHERE contract_id = contracts.id)) >= ?', [$debtFrom]);
        }

        $debtors = $query->get();

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
                'Процент оплаты',
                'Дата договора'
            ], ';');

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
                    $contract->payment_percent . '%',
                    $contract->contract_date->format('d.m.Y')
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    })->name('debtors');

    // Subjects export
    Route::get('/subjects', function(\Illuminate\Http\Request $request) {
        $query = \App\Models\Subject::with(['contracts']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('company_name', 'like', '%' . $request->search . '%')
                  ->orWhere('inn', 'like', '%' . $request->search . '%')
                  ->orWhere('pinfl', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->has('is_legal_entity') && $request->is_legal_entity !== '') {
            $query->where('is_legal_entity', $request->is_legal_entity);
        }

        $subjects = $query->get();

        $filename = 'subjects_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($subjects) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Headers
            fputcsv($file, [
                'Тип',
                'Название/ФИО',
                'ИНН/ПИНФЛ',
                'Телефон',
                'Email',
                'Адрес',
                'Количество договоров',
                'Активные договоры',
                'Дата создания'
            ], ';');

            foreach ($subjects as $subject) {
                $activeContracts = $subject->contracts->where('is_active', true)->count();
                $totalContracts = $subject->contracts->count();

                fputcsv($file, [
                    $subject->is_legal_entity ? 'Юридическое лицо' : 'Физическое лицо',
                    $subject->display_name,
                    $subject->identifier,
                    $subject->phone ?? '',
                    $subject->email ?? '',
                    $subject->legal_address ?: $subject->physical_address,
                    $totalContracts,
                    $activeContracts,
                    $subject->created_at->format('d.m.Y')
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    })->name('subjects');

    // Objects export
    Route::get('/objects', function(\Illuminate\Http\Request $request) {
        $query = \App\Models\Object::with(['subject', 'district', 'contracts']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('address', 'like', '%' . $request->search . '%')
                  ->orWhere('cadastre_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('subject', function($subQ) use ($request) {
                      $subQ->where('company_name', 'like', '%' . $request->search . '%')
                           ->orWhere('inn', 'like', '%' . $request->search . '%')
                           ->orWhere('pinfl', 'like', '%' . $request->search . '%');
                  });
            });
        }
        if ($request->district_id) {
            $query->where('district_id', $request->district_id);
        }
        if ($request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }

        $objects = $query->get();

        $filename = 'objects_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($objects) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Headers
            fputcsv($file, [
                'Адрес',
                'Заказчик',
                'Район',
                'Объем строительства (м³)',
                'Кадастровый номер',
                'Координаты',
                'Количество договоров',
                'Активные договоры',
                'Дата создания'
            ], ';');

            foreach ($objects as $object) {
                $activeContracts = $object->contracts->where('is_active', true)->count();
                $totalContracts = $object->contracts->count();

                fputcsv($file, [
                    $object->address,
                    $object->subject->display_name,
                    $object->district->name_ru,
                    $object->construction_volume,
                    $object->cadastre_number ?? '',
                    $object->geolocation ?? '',
                    $totalContracts,
                    $activeContracts,
                    $object->created_at->format('d.m.Y')
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    })->name('objects');
});

// API Routes for AJAX requests
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/contracts/{contract}/penalties', function(\App\Models\Contract $contract) {
        $contractController = new \App\Http\Controllers\ContractController();
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

        return response()->json($subjects);
    })->name('subjects.search');

    Route::get('/objects/search', function(\Illuminate\Http\Request $request) {
        $query = $request->get('q');
        $subjectId = $request->get('subject_id');

        $objectsQuery = \App\Models\Object::with(['district'])
            ->where('is_active', true);

        if ($subjectId) {
            $objectsQuery->where('subject_id', $subjectId);
        }

        if ($query) {
            $objectsQuery->where(function($q) use ($query) {
                $q->where('address', 'like', "%{$query}%")
                  ->orWhere('cadastre_number', 'like', "%{$query}%");
            });
        }

        $objects = $objectsQuery->limit(10)->get()->map(function($object) {
            return [
                'id' => $object->id,
                'text' => $object->address . ' (' . ($object->district->name_ru ?? 'Не указан') . ') - ' . number_format($object->construction_volume, 2) . ' м³',
                'volume' => $object->construction_volume,
                'district' => $object->district->name_ru ?? 'Не указан',
                'cadastre' => $object->cadastre_number
            ];
        });

        return response()->json($objects);
    })->name('objects.search');

    Route::get('/statistics/monthly', function() {
        $monthlyStats = \App\Models\ActualPayment::selectRaw('
            YEAR(payment_date) as year,
            MONTH(payment_date) as month,
            SUM(amount) as total_amount,
            COUNT(*) as payments_count
        ')
        ->groupByRaw('YEAR(payment_date), MONTH(payment_date)')
        ->orderByRaw('YEAR(payment_date) DESC, MONTH(payment_date) DESC')
        ->limit(12)
        ->get();

        return response()->json($monthlyStats);
    })->name('statistics.monthly');

    Route::get('/statistics/districts', function() {
        $districtStats = \Illuminate\Support\Facades\DB::table('contracts as c')
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
            ->groupBy('d.id', 'd.name_ru')
            ->get();

        return response()->json($districtStats);
    })->name('statistics.districts');

    Route::get('/payments/stats', function() {
        $stats = [
            'total_payments' => \App\Models\ActualPayment::count(),
            'total_amount' => \App\Models\ActualPayment::sum('amount'),
            'today_amount' => \App\Models\ActualPayment::whereDate('payment_date', today())->sum('amount'),
            'this_month' => \App\Models\ActualPayment::whereMonth('payment_date', now()->month)
                                                   ->whereYear('payment_date', now()->year)
                                                   ->sum('amount')
        ];

        return response()->json($stats);
    })->name('payments.stats');
});

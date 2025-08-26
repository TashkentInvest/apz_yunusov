<?php

namespace App\Http\Controllers;

use App\Models\KengashHulosasi;
use App\Models\KengashHulosiFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class KengashHulosasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = KengashHulosasi::with(['creator', 'updater', 'files']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by district
        if ($request->has('tuman') && !empty($request->tuman)) {
            $query->where('tuman', $request->tuman);
        }

        // Filter by building type
        if ($request->has('bino_turi') && !empty($request->bino_turi)) {
            $query->where('bino_turi', $request->bino_turi);
        }

        $kengash_hulosalari = $query->paginate(20);

        // Get filter options
        $districts = KengashHulosasi::distinct()->pluck('tuman')->filter()->sort();
        $statuses = ['Тўловдан озод этилган', 'Мажбурий тўлов'];
        $binoTurlari = ['турар', 'нотурар'];

        return view('kengash-hulosa.index', compact(
            'kengash_hulosalari',
            'districts',
            'statuses',
            'binoTurlari'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $exemptionReasons = KengashHulosasi::getExemptionReasons();
        return view('kengash-hulosa.create', compact('exemptionReasons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'kengash_hulosa_raqami' => 'nullable|string|max:255',
            'kengash_hulosa_sanasi' => 'nullable|date',
            'apz_raqami' => 'nullable|string|max:255',
            'apz_berilgan_sanasi' => 'nullable|date',
            'buyurtmachi' => 'nullable|string|max:255',
            'buyurtmachi_stir_pinfl' => 'nullable|string|max:255',
            'buyurtmachi_telefon' => 'nullable|string|max:255',
            'bino_turi' => 'nullable|in:турар,нотурар',
            'muammo_turi' => 'nullable|string|max:255',
            'loyihachi' => 'nullable|string|max:255',
            'loyihachi_stir_pinfl' => 'nullable|string|max:255',
            'loyihachi_telefon' => 'nullable|string|max:255',
            'loyiha_smeta_nomi' => 'nullable|string',
            'tuman' => 'nullable|string|max:255',
            'manzil' => 'nullable|string',
            'status' => 'required|in:Тўловдан озод этилган,Мажбурий тўлов',
            'ozod_sababi' => 'nullable|string',
            'qurilish_turi' => 'nullable|string|max:255',
            'shartnoma_raqami' => 'nullable|string|max:255',
            'shartnoma_sanasi' => 'nullable|date',
            'shartnoma_qiymati' => 'nullable|numeric|min:0',
            'fakt_tulov' => 'nullable|numeric|min:0',
            'qarzdarlik' => 'nullable|numeric|min:0',
            'tic_apz_id' => 'nullable|string|max:255',
            'files.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
            'file_comments.*' => 'nullable|string|max:500',
            'file_dates.*' => 'nullable|date'
        ]);

        $validatedData['creator_user_id'] = Auth::id();
        $validatedData['updater_user_id'] = Auth::id();

        $kengashHulosa = KengashHulosasi::create($validatedData);

        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $index => $file) {
                $this->storeFile($kengashHulosa, $file, $request, $index);
            }
        }

        return redirect()->route('kengash-hulosa.index')
            ->with('success', 'Кенгаш хулосаси муваффақиятли сақланди.');
    }

    /**
     * Display the specified resource.
     */
    public function show(KengashHulosasi $kengashHulosa)
    {
        $kengashHulosa->load(['creator', 'updater', 'files.uploader']);
        $exemptionReasons = KengashHulosasi::getExemptionReasons();

        return view('kengash-hulosa.show', compact('kengashHulosa', 'exemptionReasons'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KengashHulosasi $kengashHulosa)
    {
        $kengashHulosa->load(['files']);
        $exemptionReasons = KengashHulosasi::getExemptionReasons();

        return view('kengash-hulosa.edit', compact('kengashHulosa', 'exemptionReasons'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KengashHulosasi $kengashHulosa)
    {
        $validatedData = $request->validate([
            'kengash_hulosa_raqami' => 'nullable|string|max:255',
            'kengash_hulosa_sanasi' => 'nullable|date',
            'apz_raqami' => 'nullable|string|max:255',
            'apz_berilgan_sanasi' => 'nullable|date',
            'buyurtmachi' => 'nullable|string|max:255',
            'buyurtmachi_stir_pinfl' => 'nullable|string|max:255',
            'buyurtmachi_telefon' => 'nullable|string|max:255',
            'bino_turi' => 'nullable|in:турар,нотурар',
            'muammo_turi' => 'nullable|string|max:255',
            'loyihachi' => 'nullable|string|max:255',
            'loyihachi_stir_pinfl' => 'nullable|string|max:255',
            'loyihachi_telefon' => 'nullable|string|max:255',
            'loyiha_smeta_nomi' => 'nullable|string',
            'tuman' => 'nullable|string|max:255',
            'manzil' => 'nullable|string',
            'status' => 'required|in:Тўловдан озод этилган,Мажбурий тўлов',
            'ozod_sababi' => 'nullable|string',
            'qurilish_turi' => 'nullable|string|max:255',
            'shartnoma_raqami' => 'nullable|string|max:255',
            'shartnoma_sanasi' => 'nullable|date',
            'shartnoma_qiymati' => 'nullable|numeric|min:0',
            'fakt_tulov' => 'nullable|numeric|min:0',
            'qarzdarlik' => 'nullable|numeric|min:0',
            'tic_apz_id' => 'nullable|string|max:255',
            'files.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
            'file_comments.*' => 'nullable|string|max:500',
            'file_dates.*' => 'nullable|date'
        ]);

        $validatedData['updater_user_id'] = Auth::id();

        $kengashHulosa->update($validatedData);

        // Handle new file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $index => $file) {
                $this->storeFile($kengashHulosa, $file, $request, $index);
            }
        }

        return redirect()->route('kengash-hulosa.show', $kengashHulosa)
            ->with('success', 'Кенгаш хулосаси муваффақиятли янгиланди.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KengashHulosasi $kengashHulosa)
    {
        // Files will be automatically deleted due to model relationship
        $kengashHulosa->delete();

        return redirect()->route('kengash-hulosa.index')
            ->with('success', 'Кенгаш хулосаси муваффақиятли ўчирилди.');
    }

    /**
     * Import from Excel file
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240'
        ]);

        try {
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Skip header row
            array_shift($rows);

            $imported = 0;
            foreach ($rows as $row) {
                if (empty($row[1]) && empty($row[3])) continue; // Skip empty rows

                KengashHulosasi::create([
                    'kengash_hulosa_raqami' => $row[1] ?? null,
                    'kengash_hulosa_sanasi' => $this->parseDate($row[2] ?? null),
                    'apz_raqami' => $row[3] ?? null,
                    'apz_berilgan_sanasi' => $this->parseDate($row[4] ?? null),
                    'buyurtmachi' => $row[5] ?? null,
                    'buyurtmachi_stir_pinfl' => $row[6] ?? null,
                    'buyurtmachi_telefon' => $row[7] ?? null,
                    'bino_turi' => $this->parseBinoTuri($row[9] ?? null),
                    'muammo_turi' => $row[10] ?? null,
                    'loyihachi' => $row[11] ?? null,
                    'loyihachi_stir_pinfl' => $row[12] ?? null,
                    'loyihachi_telefon' => $row[13] ?? null,
                    'loyiha_smeta_nomi' => $row[14] ?? null,
                    'tuman' => $row[15] ?? null,
                    'manzil' => $row[16] ?? null,
                    'status' => $this->parseStatus($row[17] ?? null),
                    'qurilish_turi' => $row[18] ?? null,
                    'shartnoma_raqami' => $row[19] ?? null,
                    'shartnoma_sanasi' => $this->parseDate($row[20] ?? null),
                    'shartnoma_qiymati' => is_numeric($row[21] ?? null) ? $row[21] : null,
                    'fakt_tulov' => is_numeric($row[22] ?? null) ? $row[22] : 0,
                    'qarzdarlik' => is_numeric($row[23] ?? null) ? $row[23] : 0,
                    'tic_apz_id' => $row[24] ?? null,
                    'creator_user_id' => Auth::id(),
                    'updater_user_id' => Auth::id(),
                ]);
                $imported++;
            }

            return redirect()->route('kengash-hulosa.index')
                ->with('success', "Жами {$imported} та маълумот импорт қилинди.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Импорт қилишда хатолик: ' . $e->getMessage());
        }
    }

    /**
     * Export to Excel
     */
    public function export()
    {
        // Implementation for export functionality
        return response()->download(storage_path('app/exports/kengash-hulosa-export.xlsx'));
    }

    /**
     * Delete file
     */
    public function deleteFile(KengashHulosiFile $file)
    {
        $file->delete();

        return redirect()->back()
            ->with('success', 'Файл муваффақиятли ўчирилди.');
    }

    /**
     * Download file
     */
    public function downloadFile(KengashHulosiFile $file)
    {
        if (!Storage::exists($file->file_path)) {
            abort(404, 'Файл топилмади.');
        }

        return Storage::download($file->file_path, $file->original_name);
    }

    /**
     * Get summary statistics
     */
    public function svod()
    {
        $stats = [
            'total' => KengashHulosasi::count(),
            'ozod' => KengashHulosasi::tulovdanOzod()->count(),
            'majburiy' => KengashHulosasi::majburiyTulov()->count(),
            'total_qiymat' => KengashHulosasi::sum('shartnoma_qiymati') ?? 0,
            'total_tulov' => KengashHulosasi::sum('fakt_tulov') ?? 0,
            'total_qarz' => KengashHulosasi::sum('qarzdarlik') ?? 0,
        ];

        // Get district statistics with proper null handling
        $byDistrict = KengashHulosasi::selectRaw('
                tuman,
                count(*) as total,
                sum(case when status = "Тўловдан озод этилган" then 1 else 0 end) as ozod,
                sum(case when status = "Мажбурий тўлов" then 1 else 0 end) as majburiy,
                coalesce(sum(shartnoma_qiymati), 0) as total_qiymat,
                coalesce(sum(fakt_tulov), 0) as total_tulov,
                coalesce(sum(qarzdarlik), 0) as total_qarz
            ')
            ->where('tuman', '!=', '')
            ->whereNotNull('tuman')
            ->groupBy('tuman')
            ->orderBy('total', 'desc')
            ->get();

        // Status breakdown
        $byStatus = KengashHulosasi::selectRaw('
                status,
                count(*) as count,
                coalesce(sum(shartnoma_qiymati), 0) as total_qiymat,
                coalesce(sum(fakt_tulov), 0) as total_tulov,
                coalesce(sum(qarzdarlik), 0) as total_qarz
            ')
            ->groupBy('status')
            ->get();

        // Building types
        $byBinoTuri = KengashHulosasi::selectRaw('
                bino_turi,
                count(*) as count
            ')
            ->whereNotNull('bino_turi')
            ->where('bino_turi', '!=', '')
            ->groupBy('bino_turi')
            ->get();

        // Monthly statistics
        $monthlyStats = KengashHulosasi::selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                count(*) as count
            ')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        // Debug information
        \Log::info('Svod Stats', [
            'total_records' => $stats['total'],
            'districts_count' => $byDistrict->count(),
            'districts' => $byDistrict->pluck('tuman')->toArray(),
            'sample_district' => $byDistrict->first()
        ]);

        return view('kengash-hulosa.svod', compact(
            'stats',
            'byDistrict',
            'byStatus',
            'byBinoTuri',
            'monthlyStats'
        ));
    }

    /**
     * Store file helper method
     */
    private function storeFile($kengashHulosa, $file, $request, $index)
    {
        $originalName = $file->getClientOriginalName();
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('kengash-hulosa-files', $fileName, 'public');

        KengashHulosiFile::create([
            'kengash_hulosasi_id' => $kengashHulosa->id,
            'file_name' => $fileName,
            'original_name' => $originalName,
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'file_type' => $file->getClientMimeType(),
            'file_date' => $request->file_dates[$index] ?? now(),
            'comment' => $request->file_comments[$index] ?? null,
            'uploaded_by' => Auth::id(),
        ]);
    }

    /**
     * Parse date from Excel
     */
    private function parseDate($dateValue)
    {
        if (empty($dateValue)) return null;

        try {
            // If it's already a date object
            if ($dateValue instanceof \DateTime) {
                return $dateValue->format('Y-m-d');
            }

            // If it's a numeric Excel date
            if (is_numeric($dateValue)) {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
                return $date->format('Y-m-d');
            }

            // If it's a string date
            if (is_string($dateValue)) {
                $date = \DateTime::createFromFormat('m/d/Y', $dateValue);
                if ($date) {
                    return $date->format('Y-m-d');
                }

                $date = \DateTime::createFromFormat('d.m.Y', $dateValue);
                if ($date) {
                    return $date->format('Y-m-d');
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse building type
     */
    private function parseBinoTuri($value)
    {
        if (empty($value)) return null;

        $value = strtolower(trim($value));
        if (strpos($value, 'турар') !== false) return 'турар';
        if (strpos($value, 'нотурар') !== false) return 'нотурар';

        return null;
    }

    /**
     * Parse payment status
     */
    private function parseStatus($value)
    {
        if (empty($value)) return 'Мажбурий тўлов';

        $value = strtolower(trim($value));
        if (strpos($value, 'озод') !== false || strpos($value, 'ozod') !== false) {
            return 'Тўловдан озод этилган';
        }

        return 'Мажбурий тўлов';
    }
}

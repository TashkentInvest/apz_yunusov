<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Objectt;
use App\Models\Contract;
use App\Models\ContractStatus;
use App\Models\District;
use App\Models\BaseCalculationAmount;
use App\Models\OrgForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use ZipArchive;
use SimpleXMLElement;

class MirshodDataSeeder extends Seeder
{
    private $sharedStrings = [];
    private $skippedRows = [];
    private $unmappedDistricts = [];
    private $headerRow = [];

    // Column mapping based on the Excel structure from document
    private $columnMap = [
        'district' => 0,           // Туман
        'address' => 1,            // Манзил
        'customer' => 2,           // Буюртмачи (Customer/Company name)
        'designer' => 3,           // Лойиҳачи (Designer)
        'inn_pinfl' => 4,          // INN/PNFL
        'apz_number' => 5,         // АПЗ
        'apz_date' => 6,           // АПЗ санаси
        'object_name' => 7,        // Объект номи
    ];

    public function run()
    {
        $filePath = public_path('117_ta_malumot_mirshod.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error('Mirshod data file not found: ' . $filePath);
            return;
        }

        $this->command->info('Starting Mirshod data import from 117_ta_malumot_mirshod.xlsx...');
        Log::info('Starting Mirshod data import from 117_ta_malumot_mirshod.xlsx');

        try {
            // Ensure essential data exists
            $this->ensureEssentialData();

            $data = $this->readXlsx($filePath);

            if (empty($data)) {
                $this->command->error('No data found in XLSX file');
                return;
            }

            $this->analyzeExcelStructure($data);
            $dataRows = $this->processHeaderAndGetDataRows($data);

            // Get reference data
            $districts = $this->getDistricts();
            $pendingStatus = $this->getPendingStatus();
            $baseAmount = $this->getOrCreateBaseAmount();

            Log::info("Reference data loaded", [
                'districts' => $districts->count(),
                'pending_status' => $pendingStatus->name_uz
            ]);

            $this->processDataRows($dataRows, $districts, $pendingStatus, $baseAmount);

        } catch (\Exception $e) {
            DB::rollback();
            $errorMessage = 'Import failed: ' . $e->getMessage();
            $this->command->error($errorMessage);
            Log::error($errorMessage, ['exception' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    private function ensureEssentialData()
    {
        $this->command->info('Ensuring essential data exists...');

        // Ensure pending status exists
        $pendingStatus = ContractStatus::where('code', 'pending')->first();
        if (!$pendingStatus) {
            ContractStatus::create([
                'name_uz' => 'Кутилмоқда',
                'name_ru' => 'Ожидание',
                'code' => 'pending',
                'color' => '#ffc107',
                'is_active' => true,
            ]);
        }

        // Ensure base amount exists
        if (!BaseCalculationAmount::where('is_current', true)->exists()) {
            BaseCalculationAmount::create([
                'amount' => 412000.00,
                'effective_from' => '2024-01-01',
                'is_current' => true,
                'description' => 'Базовая расчетная величина',
            ]);
        }

        $this->command->info('Essential data verified');
    }

    private function analyzeExcelStructure($data)
    {
        $this->command->info("Excel file contains " . count($data) . " rows");
        $this->command->info("Analyzing structure...");

        // Show more rows to understand structure
        for ($i = 0; $i < min(25, count($data)); $i++) {
            $rowPreview = array_slice($data[$i], 0, 15);
            $nonEmpty = array_filter($rowPreview, function($cell) {
                return trim($cell) !== '';
            });

            if (!empty($nonEmpty)) {
                $this->command->line("Row " . ($i + 1) . " [" . count($nonEmpty) . " cells]: " . implode(' | ', $rowPreview));
            }
        }
    }

    private function processHeaderAndGetDataRows($data)
    {
        // Find header row containing "Туман", "Буюртмачи", "INN/PNFL", etc.
        $headerRowIndex = $this->findHeaderRow($data);

        if ($headerRowIndex === -1) {
            throw new \Exception('Could not find header row in Excel file');
        }

        $this->headerRow = $data[$headerRowIndex];

        // Data starts after header row
        $dataRows = array_slice($data, $headerRowIndex + 1);

        Log::info("Header analysis completed", [
            'header_row_index' => $headerRowIndex,
            'header_columns' => count($this->headerRow),
            'data_rows_remaining' => count($dataRows)
        ]);

        return $dataRows;
    }

    private function findHeaderRow($data)
    {
        // Look for row containing key headers
        for ($i = 0; $i < min(15, count($data)); $i++) {
            $row = $data[$i];

            // Count how many expected headers we find
            $foundHeaders = 0;

            foreach ($row as $cell) {
                $cellText = mb_strtolower(trim($cell));

                // Check for each expected header
                if (mb_stripos($cellText, 'туман') !== false) $foundHeaders++;
                if (mb_stripos($cellText, 'манзил') !== false) $foundHeaders++;
                if (mb_stripos($cellText, 'буюртмачи') !== false) $foundHeaders++;
                if (mb_stripos($cellText, 'лойиҳачи') !== false) $foundHeaders++;
                if (mb_stripos($cellText, 'inn') !== false || mb_stripos($cellText, 'pnfl') !== false) $foundHeaders++;
                if (mb_stripos($cellText, 'апз') !== false) $foundHeaders++;
                if (mb_stripos($cellText, 'объект') !== false) $foundHeaders++;
            }

            // If we found at least 5 of the 7 main headers, this is the header row
            if ($foundHeaders >= 5) {
                $this->command->info("Header row found at index {$i} with {$foundHeaders} headers matched");
                $this->buildColumnMap($row);
                return $i;
            }
        }

        $this->command->error("Could not find header row. Searched first 15 rows.");
        return -1;
    }

    private function buildColumnMap($headerRow)
    {
        $this->command->info("Building column map from headers...");

        foreach ($headerRow as $index => $header) {
            $header = mb_strtolower(trim($header));

            if (mb_stripos($header, 'туман') !== false) {
                $this->columnMap['district'] = $index;
                $this->command->info("  District -> Column {$index}");
            } elseif (mb_stripos($header, 'манзил') !== false || mb_stripos($header, 'адрес') !== false) {
                $this->columnMap['address'] = $index;
                $this->command->info("  Address -> Column {$index}");
            } elseif (mb_stripos($header, 'буюртмачи') !== false || mb_stripos($header, 'корхона') !== false ||
                     mb_stripos($header, 'мулкдор') !== false || mb_stripos($header, 'ташкилот') !== false) {
                $this->columnMap['customer'] = $index;
                $this->command->info("  Customer -> Column {$index}");
            } elseif (mb_stripos($header, 'лойиҳачи') !== false || mb_stripos($header, 'проект') !== false) {
                $this->columnMap['designer'] = $index;
                $this->command->info("  Designer -> Column {$index}");
            } elseif (mb_stripos($header, 'inn') !== false || mb_stripos($header, 'pnfl') !== false ||
                     mb_stripos($header, 'инн') !== false || mb_stripos($header, 'стир') !== false || mb_stripos($header, 'пинфл') !== false) {
                $this->columnMap['inn_pinfl'] = $index;
                $this->command->info("  INN/PINFL -> Column {$index}");
            } elseif (mb_stripos($header, 'апз') !== false && mb_stripos($header, 'санас') === false) {
                $this->columnMap['apz_number'] = $index;
                $this->command->info("  APZ Number -> Column {$index}");
            } elseif (mb_stripos($header, 'апз') !== false && mb_stripos($header, 'санас') !== false) {
                $this->columnMap['apz_date'] = $index;
                $this->command->info("  APZ Date -> Column {$index}");
            } elseif (mb_stripos($header, 'объект') !== false && mb_stripos($header, 'ном') !== false) {
                $this->columnMap['object_name'] = $index;
                $this->command->info("  Object Name -> Column {$index}");
            }
        }

        $this->command->info("Column map completed with " . count($this->columnMap) . " mappings");
    }

    private function processDataRows($dataRows, $districts, $pendingStatus, $baseAmount)
    {
        $processedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        $this->command->info("Processing " . count($dataRows) . " data rows...");

        // Process in chunks
        $chunks = array_chunk($dataRows, 25);

        foreach ($chunks as $chunkIndex => $chunk) {
            DB::beginTransaction();

            try {
                foreach ($chunk as $index => $row) {
                    $rowNumber = ($chunkIndex * 25) + $index + 1;

                    if ($this->isCompletelyEmptyRow($row)) {
                        $skippedCount++;
                        continue;
                    }

                    try {
                        $rowData = $this->extractRowData($row);

                        // Log every 20th row for debugging
                        if ($rowNumber % 20 == 0) {
                            Log::info("Processing row {$rowNumber}", [
                                'company_name' => $rowData['customerName'],
                                'apz_number' => $rowData['apzNumber'],
                                'inn_pinfl' => $rowData['innPinfl']
                            ]);
                        }

                        $result = $this->processMirshodRow($row, $districts, $pendingStatus, $baseAmount, $rowNumber);
                        if ($result) {
                            $processedCount++;
                            if ($processedCount % 20 == 0) {
                                $this->command->info("Processed {$processedCount} records...");
                            }
                        } else {
                            $skippedCount++;
                        }

                    } catch (\Exception $e) {
                        $errorCount++;
                        $this->handleRowError($e, $rowNumber, $row, $errorCount);

                        if ($errorCount > 50) {
                            $this->command->error("Too many errors ({$errorCount}), stopping import");
                            break 2;
                        }
                        continue;
                    }
                }

                DB::commit();
                $this->command->info("Completed chunk " . ($chunkIndex + 1) . " of " . count($chunks));

            } catch (\Exception $e) {
                DB::rollback();
                $this->command->error("Chunk {$chunkIndex} failed: " . $e->getMessage());
                throw $e;
            }
        }

        $this->showImportSummary($processedCount, $skippedCount, $errorCount);
    }

    private function handleRowError($e, $rowNumber, $row, $errorCount)
    {
        $errorMessage = "Error processing row {$rowNumber}: " . $e->getMessage();
        $this->command->warn($errorMessage);

        Log::error($errorMessage, [
            'row_number' => $rowNumber,
            'row_data' => array_slice($row, 0, 8),
            'exception' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }

    private function processMirshodRow($row, $districts, $pendingStatus, $baseAmount, $rowNumber)
    {
        $rowData = $this->extractRowData($row);

        // Skip if no meaningful data
        if (empty($rowData['customerName']) && empty($rowData['innPinfl']) && empty($rowData['apzNumber'])) {
            return false;
        }

        try {
            // Create entities in proper order
            $subject = $this->createOrFindSubject($rowData, $rowNumber);
            $object = $this->createObject($subject, $rowData, $districts, $baseAmount, $rowNumber);
            $contract = $this->createContract($subject, $object, $rowData, $pendingStatus, $baseAmount, $rowNumber);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to process row {$rowNumber}", [
                'customer_name' => $rowData['customerName'],
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function extractRowData($row)
    {
        return [
            'districtName' => $this->cleanString($row[$this->columnMap['district'] ?? 0] ?? ''),
            'address' => $this->cleanString($row[$this->columnMap['address'] ?? 1] ?? ''),
            'customerName' => $this->cleanString($row[$this->columnMap['customer'] ?? 2] ?? ''),
            'designerName' => $this->cleanString($row[$this->columnMap['designer'] ?? 3] ?? ''),
            'innPinfl' => $this->cleanString($row[$this->columnMap['inn_pinfl'] ?? 4] ?? ''),
            'apzNumber' => $this->cleanString($row[$this->columnMap['apz_number'] ?? 5] ?? ''),
            'apzDate' => $this->parseExcelDate($row[$this->columnMap['apz_date'] ?? 6] ?? ''),
            'objectName' => $this->cleanString($row[$this->columnMap['object_name'] ?? 7] ?? ''),
        ];
    }

    private function createOrFindSubject($rowData, $rowNumber)
    {
        $innPinfl = $this->cleanString($rowData['innPinfl']);
        $customerName = trim($rowData['customerName']);

        // Determine if it's INN or PINFL based on format
        $isInn = $this->looksLikeInn($innPinfl);
        $isPinfl = $this->looksLikePinfl($innPinfl);

        // Generate fallback identifier if needed
        if (empty($customerName) && empty($innPinfl)) {
            $customerName = "MIRSHOD-ROW-{$rowNumber}";
            $innPinfl = str_pad($rowNumber, 9, '0', STR_PAD_LEFT);
            $isInn = true;
        } elseif (empty($customerName)) {
            $customerName = "ID-{$innPinfl}";
        }

        // Determine if it's a legal entity based on name
        $isLegalEntity = $this->isLegalEntityByName($customerName);

        // Try to find existing subject
        $subject = null;
        if ($isInn) {
            $inn = $this->cleanIdentifier($innPinfl, 9);
            $subject = Subject::where('inn', $inn)->first();
        } elseif ($isPinfl) {
            $pinfl = $this->cleanIdentifier($innPinfl, 14);
            $subject = Subject::where('pinfl', $pinfl)->first();
        }

        if (!$subject) {
            if ($isLegalEntity || $isInn) {
                $inn = $isInn ? $this->cleanIdentifier($innPinfl, 9) : str_pad($rowNumber, 9, '0', STR_PAD_LEFT);
                $subject = Subject::create([
                    'is_legal_entity' => true,
                    'is_active' => true,
                    'country_code' => 'UZ',
                    'is_resident' => true,
                    'company_name' => $customerName,
                    'inn' => $inn,
                    'org_form_id' => $this->ensureOrgFormExists(),
                ]);
            } else {
                $pinfl = $isPinfl ? $this->cleanIdentifier($innPinfl, 14) : str_pad($rowNumber, 14, '0', STR_PAD_LEFT);
                $subject = Subject::create([
                    'is_legal_entity' => false,
                    'is_active' => true,
                    'country_code' => 'UZ',
                    'is_resident' => true,
                    'company_name' => $customerName,
                    'pinfl' => $pinfl,
                    'document_type' => 'Паспорт',
                ]);
            }
        }

        return $subject;
    }

    private function createObject($subject, $rowData, $districts, $baseAmount, $rowNumber)
    {
        $district = $this->findDistrict($rowData['districtName'], $districts, $rowNumber);
        $address = !empty($rowData['address']) ? $rowData['address'] : 'Манзил кўрсатилмаган';

        return Objectt::create([
            'subject_id' => $subject->id,
            'district_id' => $district->id,
            'address' => $address,
            'object_name' => $rowData['objectName'] ?: 'Объект номи кўрсатилмаган',
            'construction_volume' => 100, // Default volume
            'is_active' => true,
            'application_date' => $rowData['apzDate'] ?: now(),
        ]);
    }

    private function findDistrict($districtName, $districts, $rowNumber)
    {
        if (empty($districtName)) {
            return $districts->where('code', 'NOT_FOUND')->first() ?: $districts->first();
        }

        // District mappings
        $districtMappings = [
            'Олмазор' => ['Олмазор', 'Алмазар', 'Алмазарский', 'Olmazar'],
            'Мирзо-Улуғбек' => ['Мирзо-Улуғбек', 'Мирзо-Улугбек', 'Мирзо Улугбек', 'Mirzo Ulugbek', 'Мирзо-Улуғбек', 'Myrzo Ulugbek'],
            'Яккасарой' => ['Яккасарой', 'Яккасарай', 'Яккасарайский', 'Yakkasaray'],
            'Шайхонтохур' => ['Шайхонтохур', 'Шайхантахур', 'Шайхонтоҳур', 'Shayhantahur'],
            'Сергели' => ['Сергели', 'Сергелийский', 'Sergeli'],
            'Яшнобод' => ['Яшнобод', 'Яшнабад', 'Яшнободский', 'Yashnobod'],
            'Учтепа' => ['Учтепа', 'Учтепинский', 'Uchtepa'],
            'Чилонзор' => ['Чилонзор', 'Чиланзар', 'Чилонзорский', 'Chilonzor'],
            'Юнусобод' => ['Юнусобод', 'Юнусабад', 'Юнусободский', 'Yunusobod'],
            'Миробод' => ['Миробод', 'Мирабад', 'Мирободский', 'Mirobod'],
            'Янгихаёт' => ['Янгихаёт', 'Янгихайот', 'Янгиҳаёт', 'Yangihayot', 'Yangihayet'],
            'Бектемир' => ['Бектемир', 'Bektemir'],
        ];

        // Try exact match first
        $district = $districts->where('name_ru', $districtName)->first() ?:
                   $districts->where('name_uz', $districtName)->first();

        // Try mappings
        if (!$district) {
            foreach ($districtMappings as $standardName => $variations) {
                foreach ($variations as $variation) {
                    if (strcasecmp($districtName, $variation) === 0) {
                        $district = $districts->where('name_ru', $standardName)->first() ?:
                                   $districts->where('name_uz', $standardName)->first();
                        if ($district) break 2;
                    }
                }
            }
        }

        // Try partial match
        if (!$district) {
            $district = $districts->filter(function($d) use ($districtName) {
                return stripos($d->name_ru, $districtName) !== false ||
                       stripos($d->name_uz, $districtName) !== false ||
                       stripos($districtName, $d->name_ru) !== false ||
                       stripos($districtName, $d->name_uz) !== false;
            })->first();
        }

        if (!$district) {
            $this->logUnmappedDistrict($districtName, $rowNumber);
            $district = $districts->where('code', 'NOT_FOUND')->first() ?: $districts->first();
        }

        return $district;
    }

    private function createContract($subject, $object, $rowData, $pendingStatus, $baseAmount, $rowNumber)
    {
        // Use APZ number as contract number
        $contractNumber = trim($rowData['apzNumber']);
        if (empty($contractNumber)) {
            $contractNumber = 'MIRSHOD-' . $rowNumber . '/25';
        }

        // Ensure uniqueness
        $originalNumber = $contractNumber;
        $counter = 1;
        while (Contract::where('contract_number', $contractNumber)->exists()) {
            $contractNumber = $originalNumber . '-' . $counter;
            $counter++;
        }

        $contractDate = $rowData['apzDate'] ?: now();

        return Contract::create([
            'contract_number' => $contractNumber,
            'object_id' => $object->id,
            'subject_id' => $subject->id,
            'contract_date' => $contractDate,
            'completion_date' => null,
            'status_id' => $pendingStatus->id,
            'base_amount_id' => $baseAmount->id,
            'contract_volume' => $object->construction_volume,
            'coefficient' => 1.0,
            'total_amount' => 0,
            'payment_type' => 'installment',
            'initial_payment_percent' => 20.00,
            'construction_period_years' => 2,
            'quarters_count' => 8,
            'is_active' => true,
        ]);
    }

    // Utility methods
    private function looksLikeInn($value)
    {
        $cleaned = preg_replace('/[^\d]/', '', $value);
        return strlen($cleaned) >= 8 && strlen($cleaned) <= 10;
    }

    private function looksLikePinfl($value)
    {
        $cleaned = preg_replace('/[^\d]/', '', $value);
        return strlen($cleaned) >= 12 && strlen($cleaned) <= 15;
    }

    private function isLegalEntityByName($companyName)
    {
        $legalPatterns = [
            'MChJ', 'MCHJ', 'МЧЖ', 'OOO', 'ООО', 'ТОО', 'LLC', 'АЖ', 'ATB',
            'QK', 'ХК', 'Ltd', 'Inc', 'SAVDO', 'GROUP', 'INVEST', 'BANK'
        ];

        foreach ($legalPatterns as $pattern) {
            if (stripos($companyName, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    private function cleanIdentifier($value, $maxLength)
    {
        if (empty($value)) return '';

        // Handle scientific notation
        if (is_string($value) && (stripos($value, 'E+') !== false || stripos($value, 'E-') !== false)) {
            $numericValue = (float)str_replace(',', '.', $value);
            $value = number_format($numericValue, 0, '', '');
        }

        if (is_numeric($value)) {
            $value = number_format((float)$value, 0, '', '');
        }

        $cleaned = preg_replace('/[^\d]/', '', (string)$value);
        return substr($cleaned, 0, $maxLength);
    }

    private function ensureOrgFormExists()
    {
        $orgForm = OrgForm::where('is_active', true)->first();

        if (!$orgForm) {
            $orgForm = OrgForm::create([
                'name_ru' => 'ООО',
                'name_uz' => 'МЧЖ',
                'code' => 'LLC',
                'is_active' => true
            ]);
        }

        return $orgForm->id;
    }

    private function isCompletelyEmptyRow($row)
    {
        if (empty($row)) return true;

        foreach ($row as $cell) {
            if (trim($cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function logUnmappedDistrict($districtName, $rowNumber)
    {
        if (!isset($this->unmappedDistricts[$districtName])) {
            $this->unmappedDistricts[$districtName] = [];
        }
        $this->unmappedDistricts[$districtName][] = $rowNumber;
    }

    private function showImportSummary($processedCount, $skippedCount, $errorCount)
    {
        $this->command->info("\n" . str_repeat("=", 80));
        $this->command->info("MIRSHOD DATA IMPORT SUMMARY");
        $this->command->info(str_repeat("=", 80));
        $this->command->info("✅ Successfully processed: {$processedCount} records");
        $this->command->info("⏭️  Skipped rows: {$skippedCount}");
        $this->command->info("❌ Errors encountered: {$errorCount}");

        if (!empty($this->unmappedDistricts)) {
            $this->command->warn("\n⚠️  UNMAPPED DISTRICTS:");
            foreach ($this->unmappedDistricts as $districtName => $rowNumbers) {
                $count = count($rowNumbers);
                $rowList = implode(', ', array_slice($rowNumbers, 0, 10));
                $this->command->line("   📍 '{$districtName}' in {$count} rows: {$rowList}" . ($count > 10 ? ' ...' : ''));
            }
        }

        $this->command->info("\n📄 Detailed logs: storage/logs/laravel.log");
        $this->command->info("💡 All contracts imported with 'pending' status");
        $this->command->info(str_repeat("=", 80));
    }

    private function getDistricts()
    {
        return District::where('is_active', true)->get();
    }

    private function getPendingStatus()
    {
        return ContractStatus::where('code', 'pending')->firstOrFail();
    }

    private function getOrCreateBaseAmount()
    {
        return BaseCalculationAmount::where('is_current', true)->firstOrFail();
    }

    private function cleanString($value)
    {
        if (empty($value)) return '';
        return trim(preg_replace('/\s+/', ' ', (string)$value));
    }

    private function parseExcelDate($value)
    {
        if (empty($value) || $value === '-') return null;

        try {
            if (is_numeric($value) && $value > 1 && $value < 100000) {
                $excelEpoch = Carbon::create(1900, 1, 1);
                return $excelEpoch->addDays((int)$value - 2);
            }

            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value)) {
                return Carbon::createFromFormat('n/j/Y', $value);
            }

            if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $value)) {
                return Carbon::createFromFormat('d.m.Y', $value);
            }

            $formats = ['n/j/Y', 'd.m.Y', 'Y-m-d', 'd/m/Y'];
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $value);
                } catch (\Exception $e) {
                    continue;
                }
            }

            return Carbon::parse($value);
        } catch (\Exception $e) {
            Log::warning("Failed to parse date: {$value}");
            return null;
        }
    }

    // XLSX Reading Methods (same as original)
    private function readXlsx($filePath)
    {
        ini_set('memory_limit', '1G');

        $zip = new ZipArchive;

        if ($zip->open($filePath) !== TRUE) {
            throw new \Exception('Cannot open XLSX file: ' . $filePath);
        }

        try {
            $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($sharedStringsXml !== false) {
                $this->parseSharedStrings($sharedStringsXml);
            }

            $worksheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($worksheetXml === false) {
                throw new \Exception('Cannot read worksheet data from XLSX file');
            }

            return $this->parseWorksheet($worksheetXml);
        } finally {
            $zip->close();
        }
    }

    private function parseSharedStrings($xml)
    {
        try {
            $sst = new SimpleXMLElement($xml);
            $this->sharedStrings = [];

            foreach ($sst->si as $si) {
                if (isset($si->t)) {
                    $this->sharedStrings[] = (string)$si->t;
                } elseif (isset($si->r)) {
                    $richText = '';
                    foreach ($si->r as $r) {
                        if (isset($r->t)) {
                            $richText .= (string)$r->t;
                        }
                    }
                    $this->sharedStrings[] = $richText;
                } else {
                    $this->sharedStrings[] = '';
                }
            }
        } catch (\Exception $e) {
            Log::warning("Failed to parse shared strings: " . $e->getMessage());
            $this->sharedStrings = [];
        }
    }

    private function parseWorksheet($xml)
    {
        try {
            $worksheet = new SimpleXMLElement($xml);
            $data = [];

            if (!isset($worksheet->sheetData->row)) {
                return $data;
            }

            foreach ($worksheet->sheetData->row as $row) {
                $rowData = $this->parseWorksheetRow($row);
                $data[] = $rowData;
            }

            return $data;
        } catch (\Exception $e) {
            Log::error("Failed to parse worksheet: " . $e->getMessage());
            throw new \Exception("Failed to parse XLSX worksheet: " . $e->getMessage());
        }
    }

    private function parseWorksheetRow($row)
    {
        $rowData = [];
        $maxCol = 0;
        $cells = [];

        if (isset($row->c)) {
            foreach ($row->c as $cell) {
                $cellRef = (string)$cell['r'];
                $colIndex = $this->columnIndexFromString($cellRef);
                $maxCol = max($maxCol, $colIndex);

                $value = $this->parseCellValue($cell);
                $cells[$colIndex] = $value;
            }
        }

        // Fill missing columns with empty strings
        for ($i = 0; $i <= max(15, $maxCol); $i++) {
            $rowData[] = isset($cells[$i]) ? $cells[$i] : '';
        }

        return $rowData;
    }

    private function parseCellValue($cell)
    {
        $value = '';

        if (isset($cell->v)) {
            $cellValue = (string)$cell->v;
            $cellType = isset($cell['t']) ? (string)$cell['t'] : '';

            if ($cellType === 's') {
                $stringIndex = (int)$cellValue;
                $value = isset($this->sharedStrings[$stringIndex]) ? $this->sharedStrings[$stringIndex] : '';
            } elseif ($cellType === 'str') {
                $value = $cellValue;
            } elseif ($cellType === 'b') {
                $value = $cellValue === '1' ? 'TRUE' : 'FALSE';
            } else {
                $value = $cellValue;
            }
        } elseif (isset($cell->is->t)) {
            $value = (string)$cell->is->t;
        }

        return $value;
    }

    private function columnIndexFromString($cellRef)
    {
        preg_match('/^([A-Z]+)/', $cellRef, $matches);
        if (!isset($matches[1])) {
            return 0;
        }

        $column = $matches[1];
        $index = 0;
        $length = strlen($column);

        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($column[$i]) - ord('A') + 1);
        }

        return $index - 1;
    }
}

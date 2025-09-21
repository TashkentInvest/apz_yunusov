<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Objectt;
use App\Models\Contract;
use App\Models\ContractStatus;
use App\Models\District;
use App\Models\BaseCalculationAmount;
use App\Models\CouncilConclusion;
use App\Models\OrgForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use ZipArchive;
use SimpleXMLElement;

class ApzDataSeeder extends Seeder
{
    private $sharedStrings = [];
    private $skippedRows = [];
    private $unmappedDistricts = [];
    private $headerRow = [];

    // Column mapping for yunusov_art.xlsx file
    private $columnMap = [
        'serial_number' => 0,         // Т/Р
        'contract_number' => 1,       // АПЗ рақами
        'contract_date' => 2,         // санаси
        'contract_number_alt' => 3,   // Шартнома рақами
        'contract_date_alt' => 4,     // Шартнома санаси
        'company_name' => 5,          // Мулкдор
        'inn' => 6,                   // Мулкдор СТИРи
        'district' => 7,              // Туман
    ];

    public function run()
    {
        $filePath = public_path('yunusov_art.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error('APZ data file not found: ' . $filePath);
            return;
        }

        $this->command->info('Starting APZ data import from yunusov_art.xlsx...');
        Log::info('Starting APZ data import from yunusov_art.xlsx');

        try {
            // Create essential data first
            $this->createEssentialData();

            $data = $this->readXlsx($filePath);

            if (empty($data)) {
                $this->command->error('No data found in XLSX file');
                return;
            }

            $this->analyzeExcelStructure($data);
            $dataRows = $this->processHeaderAndGetDataRows($data);

            // Get reference data
            $districts = $this->getDistricts();
            $statuses = $this->getContractStatuses();
            $baseAmount = $this->getOrCreateBaseAmount();

            Log::info("Reference data loaded", [
                'districts' => $districts->count(),
                'statuses' => $statuses->count()
            ]);

            $this->processDataRows($dataRows, $districts, $statuses, $baseAmount);

        } catch (\Exception $e) {
            DB::rollback();
            $errorMessage = 'Import failed: ' . $e->getMessage();
            $this->command->error($errorMessage);
            Log::error($errorMessage, ['exception' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    private function createEssentialData()
    {
        $this->command->info('Creating essential data...');

        // Create Organization Forms
        $this->createOrgForms();

        // Create Districts
        $this->createDistricts();

        // Create Contract Statuses
        $this->createContractStatuses();

        // Create Base Calculation Amount
        $this->createBaseCalculationAmount();

        $this->command->info('Essential data created successfully');
    }

    private function createOrgForms()
    {
        $orgForms = [
            ['name_uz' => 'МЧЖ', 'name_ru' => 'ООО', 'code' => 'LLC', 'is_active' => true],
            ['name_uz' => 'АЖ', 'name_ru' => 'АО', 'code' => 'JSC', 'is_active' => true],
            ['name_uz' => 'МЧ ХК', 'name_ru' => 'ТОО', 'code' => 'LLP', 'is_active' => true],
            ['name_uz' => 'ТИФ', 'name_ru' => 'ТИФ', 'code' => 'TIF', 'is_active' => true],
        ];

        foreach ($orgForms as $orgForm) {
            OrgForm::firstOrCreate(
                ['code' => $orgForm['code']],
                $orgForm
            );
        }
    }

    private function createDistricts()
    {
        $districts = [
            ['name_uz' => 'Олмазор', 'name_ru' => 'Олмазор', 'code' => 'OLM', 'is_active' => true],
            ['name_uz' => 'Мирзо-Улуғбек', 'name_ru' => 'Мирзо-Улуғбек', 'code' => 'MUB', 'is_active' => true],
            ['name_uz' => 'Яккасарой', 'name_ru' => 'Яккасарой', 'code' => 'YAK', 'is_active' => true],
            ['name_uz' => 'Шайхонтохур', 'name_ru' => 'Шайхонтохур', 'code' => 'SHA', 'is_active' => true],
            ['name_uz' => 'Сергели', 'name_ru' => 'Сергели', 'code' => 'SER', 'is_active' => true],
            ['name_uz' => 'Яшнобод', 'name_ru' => 'Яшнобод', 'code' => 'YAS', 'is_active' => true],
            ['name_uz' => 'Учтепа', 'name_ru' => 'Учтепа', 'code' => 'UCH', 'is_active' => true],
            ['name_uz' => 'Чилонзор', 'name_ru' => 'Чилонзор', 'code' => 'CHI', 'is_active' => true],
            ['name_uz' => 'Юнусобод', 'name_ru' => 'Юнусобод', 'code' => 'YUN', 'is_active' => true],
            ['name_uz' => 'Миробод', 'name_ru' => 'Миробод', 'code' => 'MIR', 'is_active' => true],
            ['name_uz' => 'Янгихаёт', 'name_ru' => 'Янгихаёт', 'code' => 'YAN', 'is_active' => true],
            ['name_uz' => 'Бектемир', 'name_ru' => 'Бектемир', 'code' => 'BEK', 'is_active' => true],
            ['name_uz' => 'Аниқланмаган', 'name_ru' => 'Не найден', 'code' => 'NOT_FOUND', 'is_active' => true],
        ];

        foreach ($districts as $district) {
            District::firstOrCreate(
                ['name_uz' => $district['name_uz']],
                $district
            );
        }
    }

    private function createContractStatuses()
    {
        $statuses = [
            ['name_uz' => 'Амалда', 'name_ru' => 'Действующий', 'code' => 'active', 'color' => '#28a745', 'is_active' => true],
            ['name_uz' => 'Бекор қилинган', 'name_ru' => 'Отменен', 'code' => 'cancelled', 'color' => '#dc3545', 'is_active' => true],
            ['name_uz' => 'Якунланган', 'name_ru' => 'Завершен', 'code' => 'completed', 'color' => '#17a2b8', 'is_active' => true],
            ['name_uz' => 'Кутилмоқда', 'name_ru' => 'Ожидание', 'code' => 'pending', 'color' => '#ffc107', 'is_active' => true],
            ['name_uz' => 'Тўхтатилган', 'name_ru' => 'Приостановлен', 'code' => 'suspended', 'color' => '#6c757d', 'is_active' => true],
        ];

        foreach ($statuses as $status) {
            ContractStatus::firstOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }

    private function createBaseCalculationAmount()
    {
        BaseCalculationAmount::firstOrCreate(
            ['is_current' => true],
            [
                'amount' => 412000.00,
                'effective_from' => '2024-01-01',
                'is_current' => true,
                'description' => 'Базовая расчетная величина для APZ',
            ]
        );
    }

    private function analyzeExcelStructure($data)
    {
        $this->command->info("Excel file contains " . count($data) . " rows");
        $this->command->info("First 5 rows structure:");
        for ($i = 0; $i < min(5, count($data)); $i++) {
            $this->command->line("Row " . ($i + 1) . ": " . implode(' | ', array_slice($data[$i], 0, 8)));
        }
    }

    private function processHeaderAndGetDataRows($data)
    {
        // Find header row (should be row 2, index 1)
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
        // Look for the row containing "Т/Р", "АПЗ рақами", "Мулкдор", etc.
        for ($i = 0; $i < min(10, count($data)); $i++) {
            $row = $data[$i];
            $score = 0;

            // Check for key header indicators from yunusov_art.xlsx
            foreach ($row as $cell) {
                $cellText = trim($cell);
                if ($cellText === 'Т/Р' || 
                    stripos($cellText, 'АПЗ рақами') !== false ||
                    stripos($cellText, 'рақами') !== false ||
                    stripos($cellText, 'Мулкдор') !== false ||
                    stripos($cellText, 'СТИРи') !== false ||
                    stripos($cellText, 'Туман') !== false) {
                    $score++;
                }
            }

            if ($score >= 3) {
                return $i;
            }
        }
        return -1;
    }

    private function shouldSkipRow($row)
    {
        // Only skip completely empty rows with no data at all
        return $this->isCompletelyEmptyRow($row);
    }

    private function isCompletelyEmptyRow($row)
    {
        if (empty($row)) return true;

        // Check if ALL cells are empty
        foreach ($row as $cell) {
            if (trim($cell) !== '') {
                return false; // Found data, don't skip
            }
        }
        
        return true; // All cells empty, skip this row
    }

    private function processDataRows($dataRows, $districts, $statuses, $baseAmount)
    {
        $processedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        $this->command->info("Processing " . count($dataRows) . " data rows...");

        // Process in smaller chunks for better error handling
        $chunks = array_chunk($dataRows, 25);

        foreach ($chunks as $chunkIndex => $chunk) {
            DB::beginTransaction();

            try {
                foreach ($chunk as $index => $row) {
                    $rowNumber = ($chunkIndex * 25) + $index + 1;

                    if ($this->shouldSkipRow($row)) {
                        $skippedCount++;
                        $this->logSkippedRow($rowNumber, 'Skipped row (empty/summary)', $row);
                        continue;
                    }

                    try {
                        $rowData = $this->extractRowData($row);
                        
                        // Log every 50th row for debugging
                        if ($rowNumber % 50 == 0) {
                            Log::info("Processing row {$rowNumber}", [
                                'company_name' => $rowData['companyName'],
                                'contract_number' => $rowData['contractNumber'],
                                'inn' => $rowData['inn'],
                                'pinfl' => $rowData['pinfl']
                            ]);
                        }

                        // PROCESS ALL ROWS - no validation skip
                        $result = $this->processApzRow($row, $districts, $statuses, $baseAmount, $rowNumber);
                        if ($result) {
                            $processedCount++;
                            if ($processedCount % 25 == 0) {
                                $this->command->info("Processed {$processedCount} records...");
                            }
                        } else {
                            $skippedCount++;
                        }

                    } catch (\Exception $e) {
                        $errorCount++;
                        $this->handleRowError($e, $rowNumber, $row, $errorCount);
                        
                        // Don't stop processing, just log and continue
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
            'row_data' => array_slice($row, 0, 15),
            'exception' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        if ($errorCount <= 5) {
            $this->command->error("Details: " . $e->getFile() . ':' . $e->getLine());
        }
    }

    private function processApzRow($row, $districts, $statuses, $baseAmount, $rowNumber)
    {
        $rowData = $this->extractRowData($row);

        // Debug logging for first few rows
        if ($rowNumber <= 3) {
            Log::info("Row {$rowNumber} extracted data", [
                'company_name' => $rowData['companyName'],
                'contract_number' => $rowData['contractNumber'],
                'inn' => $rowData['inn'],
                'pinfl' => $rowData['pinfl'],
                'district' => $rowData['districtName']
            ]);
        }

        try {
            // Create entities in proper order
            $subject = $this->createOrFindSubject($rowData, $rowNumber);
            $object = $this->createObject($subject, $rowData, $districts, $baseAmount, $rowNumber);
            $contract = $this->createContract($subject, $object, $rowData, $statuses, $baseAmount, $rowNumber);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to process row {$rowNumber}", [
                'company_name' => $rowData['companyName'],
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function extractRowData($row)
    {
        return [
            'serialNumber' => $this->cleanString($row[$this->columnMap['serial_number']] ?? ''),
            'contractNumber' => $this->cleanString($row[$this->columnMap['contract_number']] ?? ''),
            'contractDate' => $this->parseExcelDate($row[$this->columnMap['contract_date']] ?? ''),
            'companyName' => $this->cleanString($row[$this->columnMap['company_name']] ?? ''),
            'inn' => $this->cleanIdentifier($row[$this->columnMap['inn']] ?? '', 9),
            'pinfl' => '', // Not available in this format
            'districtName' => $this->cleanString($row[$this->columnMap['district']] ?? ''),
            'contractStatus' => 'Амалда', // Default status since not in data
            'completionDate' => null, // Not available
            'paymentTerms' => '20/80', // Default payment terms
            'paymentPeriod' => 8, // Default 8 quarters
            'area' => 100, // Default area
        ];
    }

    private function validateRowData($rowNumber, $rowData)
    {
        // Process ALL rows with any data - no validation skipping
        return ['valid' => true, 'reason' => null];
    }

    private function looksLikeHeaderRow($rowData)
    {
        $headerKeywords = ['инн', 'пинфл', 'корхона', 'шарт', 'контракт', 'санаси', 'номи', 'ҳолати'];
        $keywordCount = 0;

        foreach ($rowData as $value) {
            $text = strtolower(trim($value));
            foreach ($headerKeywords as $keyword) {
                if (stripos($text, $keyword) !== false) {
                    $keywordCount++;
                    break;
                }
            }
        }

        return $keywordCount >= 3;
    }

    private function containsExcelErrors($rowData)
    {
        foreach ($rowData as $value) {
            $text = trim($value);
            if (stripos($text, '#REF') !== false || 
                stripos($text, '#N/A') !== false || 
                stripos($text, '#DIV/0') !== false ||
                stripos($text, '#VALUE') !== false) {
                return true;
            }
        }
        return false;
    }

    private function hasAnyMeaningfulData($rowData)
    {
        // Check if we have at least one of: company name, INN, PINFL, or contract number
        $companyName = trim($rowData['companyName'] ?? '');
        $inn = trim($rowData['inn'] ?? '');
        $pinfl = trim($rowData['pinfl'] ?? '');
        $contractNumber = trim($rowData['contractNumber'] ?? '');

        return !empty($companyName) || !empty($inn) || !empty($pinfl) || !empty($contractNumber);
    }

    private function createOrFindSubject($rowData, $rowNumber)
    {
        $inn = $this->cleanIdentifier($rowData['inn'], 9);
        $companyName = trim($rowData['companyName']);

        // Use actual data from file - no generation unless completely missing
        if (empty($companyName) && empty($inn)) {
            $companyName = "APT-ROW-{$rowNumber}";
            $inn = str_pad($rowNumber, 9, '0', STR_PAD_LEFT);
        } elseif (empty($companyName)) {
            $companyName = "INN-{$inn}";
        } elseif (empty($inn)) {
            // Check if it looks like PINFL (individual)
            if (preg_match('/[A-Z]{2}\d+/', $companyName) || 
                stripos($companyName, "o'g'li") !== false || 
                stripos($companyName, "qizi") !== false) {
                // This is an individual, use PINFL
                $pinfl = $this->cleanIdentifier($inn, 14); // Use inn field as pinfl for individuals
                return $this->createIndividualSubject($companyName, $pinfl ?: str_pad($rowNumber, 14, '0', STR_PAD_LEFT));
            } else {
                $inn = str_pad($rowNumber, 9, '0', STR_PAD_LEFT);
            }
        }

        // Try to find existing subject
        $subject = Subject::where('inn', $inn)->first();

        if (!$subject) {
            $subject = Subject::create([
                'is_legal_entity' => true,
                'is_active' => true,
                'country_code' => 'UZ',
                'is_resident' => true,
                'company_name' => $companyName,
                'inn' => $inn,
                'org_form_id' => $this->ensureOrgFormExists(),
            ]);
        }

        return $subject;
    }

    private function createIndividualSubject($fullName, $pinfl)
    {
        $subject = Subject::where('pinfl', $pinfl)->first();

        if (!$subject) {
            $subject = Subject::create([
                'is_legal_entity' => false,
                'is_active' => true,
                'country_code' => 'UZ',
                'is_resident' => true,
                'company_name' => $fullName,
                'pinfl' => $pinfl,
                'document_type' => 'Паспорт',
            ]);
        }

        return $subject;
    }

    private function createNewSubject($identifier, $companyName, $isLegalEntity, $inn = null, $pinfl = null)
    {
        $subjectData = [
            'is_legal_entity' => $isLegalEntity,
            'is_active' => true,
            'country_code' => 'UZ',
            'is_resident' => true,
            'company_name' => $companyName,
        ];

        if ($isLegalEntity) {
            $subjectData['inn'] = $inn ?: $identifier;
            $subjectData['org_form_id'] = $this->ensureOrgFormExists();
            // Also store PINFL if it exists for legal entity
            if (!empty($pinfl)) {
                $subjectData['pinfl'] = $pinfl;
            }
        } else {
            $subjectData['pinfl'] = $pinfl ?: $identifier;
            $subjectData['document_type'] = 'Паспорт';
            // Also store INN if it exists for individual
            if (!empty($inn)) {
                $subjectData['inn'] = $inn;
            }
        }

        return Subject::create($subjectData);
    }

    private function createObject($subject, $rowData, $districts, $baseAmount, $rowNumber)
    {
        $district = $this->findDistrict($rowData['districtName'], $districts, $rowNumber);

        // Use area if available, otherwise default to 1
        $volume = $rowData['area'] > 0 ? $rowData['area'] : 1;

        return Objectt::create([
            'subject_id' => $subject->id,
            'district_id' => $district->id,
            'address' => 'APZ импорт - ' . ($rowData['districtName'] ?: 'Адрес не указан'),
            'construction_volume' => $volume,
            'is_active' => true,
            'application_date' => $rowData['contractDate'] ?: now(),
        ]);
    }

    private function findDistrict($districtName, $districts, $rowNumber)
    {
        if (empty($districtName)) {
            // Use "not found" district instead of fallback
            return $districts->where('code', 'NOT_FOUND')->first() ?: $districts->first();
        }

        // District mappings for common variations
        $districtMappings = [
            'Олмазор' => ['Олмазор', 'Алмазар', 'Алмазарский', 'Olmazar'],
            'Мирзо-Улуғбек' => ['Мирзо-Улуғбек', 'Мирзо-Улугбек', 'Мирзо-Улугбекский', 'Mirzo-Ulugbek'],
            'Яккасарой' => ['Яккасарой', 'Яккасарай', 'Яккасарайский', 'Yakkasaray'],
            'Шайхонтохур' => ['Шайхонтохур', 'Шайхантахур', 'Шайхантахурский', 'Shayhantahur'],
            'Сергели' => ['Сергели', 'Сергелийский', 'Sergeli'],
            'Яшнобод' => ['Яшнобод', 'Яшнабад', 'Яшнободский', 'Yashnobod'],
            'Учтепа' => ['Учтепа', 'Учтепинский', 'Uchtepa'],
            'Чилонзор' => ['Чилонзор', 'Чиланзар', 'Чилонзорский', 'Chilonzor'],
            'Юнусобод' => ['Юнусобод', 'Юнусабад', 'Юнусободский', 'Yunusobod'],
            'Миробод' => ['Миробод', 'Мирабад', 'Мирободский', 'Mirobod'],
            'Янгихаёт' => ['Янгихаёт', 'Янгихайот', 'Yangihayet'],
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

        // Use "not found" district if no match
        if (!$district) {
            $this->logUnmappedDistrict($districtName, $rowNumber);
            $district = $districts->where('code', 'NOT_FOUND')->first() ?: $districts->first();
        }

        return $district;
    }

    private function logUnmappedDistrict($districtName, $rowNumber)
    {
        if (!isset($this->unmappedDistricts[$districtName])) {
            $this->unmappedDistricts[$districtName] = [];
        }
        $this->unmappedDistricts[$districtName][] = $rowNumber;
    }

    private function createContract($subject, $object, $rowData, $statuses, $baseAmount, $rowNumber)
    {
        // Use actual contract number from data
        $contractNumber = trim($rowData['contractNumber']);
        if (empty($contractNumber)) {
            $contractNumber = 'APT-' . $rowNumber . '/24'; // Follow APT pattern from your data
        }

        // Ensure uniqueness
        $originalNumber = $contractNumber;
        $counter = 1;
        while (Contract::where('contract_number', $contractNumber)->exists()) {
            $contractNumber = $originalNumber . '-DUP-' . $counter;
            $counter++;
        }

        // Use actual payment terms and periods from data
        $paymentType = 'installment';
        if (!empty($rowData['paymentTerms'])) {
            if (stripos($rowData['paymentTerms'], '100') !== false) {
                $paymentType = 'full';
            }
        }

        $status = $this->getContractStatus($rowData['contractStatus'], $statuses);
        $isActive = !$this->isContractCancelled($rowData['contractStatus']);

        // Use actual dates from data or reasonable defaults
        $contractDate = $rowData['contractDate'] ?: now();
        
        // Use actual payment period from data
        $constructionPeriodYears = $rowData['paymentPeriod'] > 0 ?
            max(1, ceil($rowData['paymentPeriod'] / 12)) : 2;
        $quartersCount = $rowData['paymentPeriod'] > 0 ?
            max(1, ceil($rowData['paymentPeriod'] / 3)) : 8;

        // Extract initial payment percentage from payment terms
        $initialPaymentPercent = 20.00; // default
        if (!empty($rowData['paymentTerms'])) {
            if (preg_match('/(\d+)/', $rowData['paymentTerms'], $matches)) {
                $percent = (float)$matches[1];
                if ($percent <= 100 && $percent > 0) {
                    $initialPaymentPercent = $percent;
                }
            }
        }

        return Contract::create([
            'contract_number' => $contractNumber,
            'object_id' => $object->id,
            'subject_id' => $subject->id,
            'contract_date' => $contractDate,
            'completion_date' => $rowData['completionDate'],
            'status_id' => $status->id,
            'base_amount_id' => $baseAmount->id,
            'contract_volume' => $object->construction_volume,
            'coefficient' => 1.0,
            'total_amount' => 0, // Import without amounts as requested
            'payment_type' => $paymentType,
            'initial_payment_percent' => $initialPaymentPercent,
            'construction_period_years' => $constructionPeriodYears,
            'quarters_count' => $quartersCount,
            'is_active' => $isActive,
        ]);
    }

    private function getContractStatus($contractStatus, $statuses)
    {
        if (!empty($contractStatus)) {
            // Check for cancelled status
            if (stripos($contractStatus, 'Бекор') !== false || stripos($contractStatus, 'қилинган') !== false) {
                $cancelled = $statuses->filter(function($s) {
                    return stripos($s->name_ru, 'Бекор') !== false ||
                           stripos($s->name_uz, 'Бекор') !== false ||
                           stripos($s->name_ru, 'Отмен') !== false;
                })->first();
                if ($cancelled) return $cancelled;
            }
        }

        // Default to active status
        return $statuses->filter(function($s) {
            return stripos($s->name_ru, 'Действ') !== false ||
                   stripos($s->name_uz, 'Амал') !== false ||
                   stripos($s->name_ru, 'Активн') !== false;
        })->first() ?: $statuses->first();
    }

    private function isContractCancelled($contractStatus)
    {
        return !empty($contractStatus) && (
            stripos($contractStatus, 'Бекор') !== false ||
            stripos($contractStatus, 'қилинган') !== false ||
            stripos($contractStatus, 'отменен') !== false ||
            stripos($contractStatus, 'cancelled') !== false
        );
    }

    // Utility methods for data validation and cleaning
    private function cleanIdentifier($value, $maxLength)
    {
        if (empty($value)) return '';

        // Handle scientific notation (e.g., 3,07058E+13)
        if (is_string($value) && (stripos($value, 'E+') !== false || stripos($value, 'E-') !== false)) {
            $numericValue = (float)str_replace(',', '.', $value);
            $value = number_format($numericValue, 0, '', '');
        }

        // Handle numeric values
        if (is_numeric($value)) {
            $value = number_format((float)$value, 0, '', '');
        }

        $cleaned = preg_replace('/[^\d]/', '', (string)$value);
        return substr($cleaned, 0, $maxLength);
    }

    private function isLegalEntityByName($companyName)
    {
        $legalPatterns = [
            'OOO', 'МЧЖ', 'MCHJ', 'ООО', 'ТОО', 'LLC', 'АЖ', 'СП', 'ТИФ',
            'Ltd', 'Inc', 'МЧ ХК', 'XK', 'САВДО', 'TRADE', 'BUSINESS'
        ];

        foreach ($legalPatterns as $pattern) {
            if (stripos($companyName, $pattern) !== false) {
                return true;
            }
        }

        return false;
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

    private function logSkippedRow($rowNumber, $reason, $row)
    {
        if (!isset($this->skippedRows[$reason])) {
            $this->skippedRows[$reason] = [];
        }

        $this->skippedRows[$reason][] = [
            'row' => $rowNumber,
            'sample_data' => [
                'company_name' => $row[$this->columnMap['company_name']] ?? '',
                'contract_number' => $row[$this->columnMap['contract_number']] ?? '',
                'inn' => $row[$this->columnMap['inn']] ?? '',
                'district' => $row[$this->columnMap['district']] ?? ''
            ]
        ];
    }

    private function showImportSummary($processedCount, $skippedCount, $errorCount)
    {
        $this->command->info("\n" . str_repeat("=", 80));
        $this->command->info("APZ ESSENTIAL DATA IMPORT SUMMARY");
        $this->command->info(str_repeat("=", 80));
        $this->command->info("✅ Successfully processed: {$processedCount} records");
        $this->command->info("⏭️  Skipped rows: {$skippedCount}");
        $this->command->info("❌ Errors encountered: {$errorCount}");

        if (!empty($this->unmappedDistricts)) {
            $this->showUnmappedDistricts();
        }

        if ($skippedCount > 0) {
            $this->showSkippedRowsDetails();
        }

        $this->showAvailableDistricts();
        $this->showRecommendations();
    }

    private function showUnmappedDistricts()
    {
        $this->command->warn("\n⚠️  UNMAPPED DISTRICTS FOUND:");
        foreach ($this->unmappedDistricts as $districtName => $rowNumbers) {
            $count = count($rowNumbers);
            $rowList = implode(', ', array_slice($rowNumbers, 0, 10));
            $this->command->line("   📍 '{$districtName}' in {$count} rows: {$rowList}" . ($count > 10 ? ' ...' : ''));
        }
    }

    private function showSkippedRowsDetails()
    {
        $this->command->warn("\n📋 SKIPPED ROWS DETAILS:");
        foreach ($this->skippedRows as $reason => $rows) {
            $count = count($rows);
            $this->command->line("\n   ❌ {$reason}: {$count} rows");

            $rowsToShow = array_slice($rows, 0, 5);
            foreach ($rowsToShow as $rowData) {
                $sample = $rowData['sample_data'];
                $companyName = !empty($sample['company_name']) ? $sample['company_name'] : 'N/A';
                $contractNum = !empty($sample['contract_number']) ? $sample['contract_number'] : 'N/A';
                $this->command->line("      Row {$rowData['row']}: {$companyName} | Contract: {$contractNum}");
            }

            if ($count > 5) {
                $this->command->line("      ... and " . ($count - 5) . " more rows");
            }
        }
    }

    private function showAvailableDistricts()
    {
        $this->command->info("\n📍 AVAILABLE DISTRICTS IN DATABASE:");
        $availableDistricts = District::where('is_active', true)->pluck('name_ru')->toArray();
        $this->command->line("   " . implode(', ', $availableDistricts));
    }

    private function showRecommendations()
    {
        $this->command->info("\n📄 Detailed logs: storage/logs/laravel.log");
        $this->command->info("🔧 To fix unmapped districts, either:");
        $this->command->info("   1. Add missing districts to database, OR");
        $this->command->info("   2. Update Excel file with correct district names");
        $this->command->info("\n💡 NOTE: This seeder imports only essential data:");
        $this->command->info("   - Company/Individual information (Subject)");
        $this->command->info("   - Construction objects with basic details");
        $this->command->info("   - Contract records without financial amounts");
        $this->command->info("   - No payment schedules or actual payments created");
        $this->command->info(str_repeat("=", 80));
    }

    // Data access methods
    private function getDistricts()
    {
        return District::where('is_active', true)->get();
    }

    private function getContractStatuses()
    {
        return ContractStatus::where('is_active', true)->get();
    }

    private function getOrCreateBaseAmount()
    {
        return BaseCalculationAmount::where('is_current', true)->first();
    }

    // Row validation methods
    private function isHeaderRow($row)
    {
        $headerKeywords = ['ИНН', 'ПИНФЛ', 'Корхона', 'шарт', 'Контракт', 'санаси', 'тўлов', 'факт', 'план', 'номи'];

        $keywordCount = 0;
        foreach ($row as $cell) {
            $cellText = strtolower(trim($cell));
            foreach ($headerKeywords as $keyword) {
                if (stripos($cellText, strtolower($keyword)) !== false) {
                    $keywordCount++;
                    break;
                }
            }
        }

        return $keywordCount >= 3;
    }

    // XLSX Reading Methods
    private function readXlsx($filePath)
    {
        // Increase memory limit for large files
        ini_set('memory_limit', '1G');

        $zip = new ZipArchive;

        if ($zip->open($filePath) !== TRUE) {
            throw new \Exception('Cannot open XLSX file: ' . $filePath);
        }

        try {
            // Read shared strings
            $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($sharedStringsXml !== false) {
                $this->parseSharedStrings($sharedStringsXml);
            }

            // Read worksheet
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

        // Fill missing columns with empty strings (extend to at least 20 columns for basic data)
        for ($i = 0; $i <= max(20, $maxCol); $i++) {
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
                // Shared string
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

        return $index - 1; // Convert to 0-based index
    }

    // Data parsing utility methods
    private function cleanString($value)
    {
        if (empty($value)) return '';
        return trim(preg_replace('/\s+/', ' ', (string)$value));
    }

    private function parseExcelDate($value)
    {
        if (empty($value) || $value === '-' || $value === '00.01.1900') return null;

        try {
            // Handle Excel serial date numbers
            if (is_numeric($value) && $value > 1 && $value < 100000) {
                // Excel epoch starts at 1900-01-01, but Excel incorrectly treats 1900 as leap year
                $excelEpoch = Carbon::create(1900, 1, 1);
                return $excelEpoch->addDays((int)$value - 2);
            }

            // Handle M/D/YYYY format (4/30/2024)
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $matches)) {
                return Carbon::createFromFormat('n/j/Y', $value);
            }

            // Handle DD.MM.YYYY format
            if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $value, $matches)) {
                return Carbon::createFromFormat('d.m.Y', $value);
            }

            // Try common date formats
            $formats = ['n/j/Y', 'd.m.Y', 'Y-m-d', 'd/m/Y', 'm/d/Y', 'Y/m/d'];
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

    private function parseAmount($value)
    {
        if (empty($value) || $value === '-' || stripos($value, '#REF') !== false || stripos($value, '#N/A') !== false) {
            return 0;
        }

        // Handle scientific notation (e.g., 2,13272E+12)
        if (is_string($value) && (stripos($value, 'E+') !== false || stripos($value, 'E-') !== false)) {
            return (float)str_replace(',', '.', $value);
        }

        // Handle numeric values
        if (is_numeric($value)) {
            return (float)$value;
        }

        // Handle comma-separated numbers (2,132,715,475,659)
        $cleaned = str_replace(',', '', (string)$value);
        $cleaned = preg_replace('/[^\d.-]/', '', $cleaned);

        return (float)$cleaned;
    }
}
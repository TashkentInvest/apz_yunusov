<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Objectt;
use App\Models\Contract;
use App\Models\ContractStatus;
use App\Models\District;
use App\Models\BaseCalculationAmount;
use App\Models\ActualPayment;
use App\Models\PaymentSchedule;
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
    private $monthlyPaymentColumns = [];

    // Fixed column indexes based on your data structure
    private $columnMap = [
        'council_conclusion_1' => 0,  // Умуман тўловни амалга оширмаганлар
        'council_conclusion_2' => 2,  // Қарздорлар
        'serial_number' => 3,         // №
        'inn' => 4,                   // ИНН
        'pinfl' => 5,                 // ПИНФЛ
        'company_name' => 6,          // Корхона номи
        'contract_number' => 7,       // шарт. №
        'contract_status' => 8,       // Контракт ҳолати
        'contract_date' => 9,         // шартнома санаси
        'completion_date' => 10,      // Якунлаш сана
        'payment_terms' => 11,        // Тўлов шарти
        'payment_period' => 12,       // Тўлов муддати
        'advance_percent' => 13,      // Аванс
        'district' => 14,             // Туман
        'area' => 15,                 // М3 (площадь)
        'contract_amount' => 16,      // Шартнома қиймати
        'installment_payment' => 17,  // Бўнак тўлов
        'monthly_payment' => 18,      // Ойлик тўлов
        'total_payment' => 19,        // Жами тўлов
        'remaining' => 20,            // Қолдиқ
        'actual_payment_indicator' => 21, // ФАКТ
    ];

    public function run()
    {
        $filePath = public_path('apz_dilmurod_23_08_25.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error('APZ data file not found: ' . $filePath);
            return;
        }

        $this->command->info('Starting APZ data import from XLSX...');
        Log::info('Starting APZ data import from XLSX');

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
        $this->identifyMonthlyPaymentColumns();

        // Data starts after header row
        $dataRows = array_slice($data, $headerRowIndex + 1);

        Log::info("Header analysis completed", [
            'header_row_index' => $headerRowIndex,
            'header_columns' => count($this->headerRow),
            'monthly_payment_columns' => count($this->monthlyPaymentColumns),
            'data_rows_remaining' => count($dataRows)
        ]);

        return $dataRows;
    }

    private function findHeaderRow($data)
    {
        // Look for the row containing "№", "ИНН", "ПИНФЛ", etc.
        for ($i = 0; $i < min(10, count($data)); $i++) {
            $row = $data[$i];
            $score = 0;

            // Check for key header indicators
            foreach ($row as $cell) {
                $cellText = trim($cell);
                if ($cellText === '№' || $cellText === 'ИНН' || $cellText === 'ПИНФЛ' ||
                    stripos($cellText, 'Корхона номи') !== false ||
                    stripos($cellText, 'шарт. №') !== false) {
                    $score++;
                }
            }

            if ($score >= 3) {
                return $i;
            }
        }
        return -1;
    }

    private function identifyMonthlyPaymentColumns()
    {
        $this->monthlyPaymentColumns = [];

        // Start searching from column 22 onwards
        for ($i = 22; $i < count($this->headerRow); $i++) {
            $headerText = trim($this->headerRow[$i]);

            // Debug log each column header
            if ($i < 35) {
                Log::info("Checking column {$i}: '{$headerText}'");
            }

            // Check for M/D/YYYY format (like 4/30/2024)
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $headerText)) {
                $this->monthlyPaymentColumns[$i] = [
                    'date' => $headerText,
                    'parsed_date' => $this->parseExcelDateFromString($headerText)
                ];
                Log::info("Found M/D/YYYY date column at {$i}: {$headerText}");
            }
            // Also check for DD.MM.YYYY format (like 30.04.2024)
            elseif (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $headerText)) {
                $this->monthlyPaymentColumns[$i] = [
                    'date' => $headerText,
                    'parsed_date' => $this->parseExcelDateFromString($headerText)
                ];
                Log::info("Found DD.MM.YYYY date column at {$i}: {$headerText}");
            }
            // Check for Excel serial numbers that represent dates (45412 = April 30, 2024)
            elseif (is_numeric($headerText) && $headerText > 40000 && $headerText < 50000) {
                $parsedDate = $this->parseExcelSerialDate($headerText);
                if ($parsedDate) {
                    $this->monthlyPaymentColumns[$i] = [
                        'date' => $parsedDate->format('n/j/Y'),
                        'parsed_date' => $parsedDate,
                        'serial' => $headerText
                    ];
                    Log::info("Found Excel serial date column at {$i}: {$headerText} = {$parsedDate->format('M d, Y')}");
                }
            }

            // Stop when we hit summary columns
            if (stripos($headerText, 'ЖАМИ') !== false ||
                stripos($headerText, 'ПЛАН') !== false ||
                stripos($headerText, 'Реальная') !== false) {
                break;
            }
        }

        Log::info("Monthly payment columns detected", [
            'count' => count($this->monthlyPaymentColumns),
            'sample_dates' => array_slice(array_column($this->monthlyPaymentColumns, 'date'), 0, 5)
        ]);
    }

    private function processDataRows($dataRows, $districts, $statuses, $baseAmount)
    {
        $processedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        // Process in chunks to handle large datasets
        $chunks = array_chunk($dataRows, 50);

        foreach ($chunks as $chunkIndex => $chunk) {
            DB::beginTransaction();

            try {
                foreach ($chunk as $index => $row) {
                    $rowNumber = ($chunkIndex * 50) + $index + 1;

                    if ($this->shouldSkipRow($row)) {
                        $skippedCount++;
                        $this->logSkippedRow($rowNumber, 'Empty or summary row', $row);
                        continue;
                    }

                    try {
                        $result = $this->processApzRow($row, $districts, $statuses, $baseAmount, $rowNumber);
                        if ($result) {
                            $processedCount++;
                            if ($processedCount % 10 == 0) {
                                $this->command->info("Processed {$processedCount} records...");
                            }
                        } else {
                            $skippedCount++;
                        }
                    } catch (\Exception $e) {
                        $errorCount++;
                        $this->handleRowError($e, $rowNumber, $row, $errorCount);
                        continue;
                    }
                }

                DB::commit();
                $this->command->info("Completed chunk " . ($chunkIndex + 1) . " of " . count($chunks));

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        }

        $this->showImportSummary($processedCount, $skippedCount, $errorCount);
    }

    private function shouldSkipRow($row)
    {
        return $this->isEmptyRow($row) || $this->isSummaryRow($row) || $this->isHeaderRow($row);
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
                'contract_amount' => $rowData['contractAmount'],
                'inn' => $rowData['inn'],
                'pinfl' => $rowData['pinfl'],
                'monthly_payments_count' => count($rowData['monthlyPayments']),
                'total_actual_payment' => $rowData['totalActualPayment']
            ]);
        }

        // Validate essential data
        $validationResult = $this->validateRowData($rowNumber, $rowData);
        if (!$validationResult['valid']) {
            $this->logSkippedRow($rowNumber, $validationResult['reason'], $row);
            return false;
        }

        try {
            // Create entities in proper order
            $subject = $this->createOrFindSubject($rowData, $rowNumber);
            $object = $this->createObject($subject, $rowData, $districts, $baseAmount, $rowNumber);
            $contract = $this->createContract($subject, $object, $rowData, $statuses, $baseAmount, $rowNumber);

            // Create related data
            $this->createPaymentSchedule($contract, $rowData, $rowNumber);
            $this->createActualPayments($contract, $rowData, $rowNumber);
            $this->createCouncilConclusion($object, $rowData, $rowNumber);

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
        // Extract monthly payments from date columns
        $monthlyPayments = [];
        foreach ($this->monthlyPaymentColumns as $colIndex => $monthInfo) {
            $payment = $this->parseAmount($row[$colIndex] ?? 0);
            if ($payment > 0) {
                $monthlyPayments[$monthInfo['date']] = $payment;
            }
        }

        // Calculate total actual payment
        $totalActualPayment = array_sum($monthlyPayments);

        return [
            'councilConclusion1' => $this->cleanString($row[$this->columnMap['council_conclusion_1']] ?? ''),
            'councilConclusion2' => $this->cleanString($row[$this->columnMap['council_conclusion_2']] ?? ''),
            'serialNumber' => $this->cleanString($row[$this->columnMap['serial_number']] ?? ''),
            'inn' => $this->cleanString($row[$this->columnMap['inn']] ?? ''),
            'pinfl' => $this->cleanString($row[$this->columnMap['pinfl']] ?? ''),
            'companyName' => $this->cleanString($row[$this->columnMap['company_name']] ?? ''),
            'contractNumber' => $this->cleanString($row[$this->columnMap['contract_number']] ?? ''),
            'contractStatus' => $this->cleanString($row[$this->columnMap['contract_status']] ?? ''),
            'contractDate' => $this->parseExcelDate($row[$this->columnMap['contract_date']] ?? ''),
            'completionDate' => $this->parseExcelDate($row[$this->columnMap['completion_date']] ?? ''),
            'paymentTerms' => $this->cleanString($row[$this->columnMap['payment_terms']] ?? ''),
            'paymentPeriod' => (int)($row[$this->columnMap['payment_period']] ?? 0),
            'advancePercent' => $this->parsePercent($row[$this->columnMap['advance_percent']] ?? ''),
            'districtName' => $this->cleanString($row[$this->columnMap['district']] ?? ''),
            'area' => $this->parseAmount($row[$this->columnMap['area']] ?? 0),
            'contractAmount' => $this->parseAmount($row[$this->columnMap['contract_amount']] ?? 0),
            'installmentPayment' => $this->parseAmount($row[$this->columnMap['installment_payment']] ?? 0),
            'monthlyPayment' => $this->parseAmount($row[$this->columnMap['monthly_payment']] ?? 0),
            'totalPayment' => $this->parseAmount($row[$this->columnMap['total_payment']] ?? 0),
            'remaining' => $this->parseAmount($row[$this->columnMap['remaining']] ?? 0),
            'monthlyPayments' => $monthlyPayments,
            'totalActualPayment' => $totalActualPayment
        ];
    }

    private function validateRowData($rowNumber, $rowData)
    {
        // Skip completely empty rows
        if (empty($rowData['companyName']) && empty($rowData['inn']) && empty($rowData['pinfl'])) {
            return ['valid' => false, 'reason' => 'Completely empty row'];
        }

        // Skip cancelled contracts with no amounts
        if (stripos($rowData['contractStatus'], 'Бекор') !== false && $rowData['contractAmount'] == 0) {
            return ['valid' => false, 'reason' => 'Cancelled contract with zero amount'];
        }

        if (empty($rowData['companyName'])) {
            return ['valid' => false, 'reason' => 'Missing company name'];
        }

        if (empty($rowData['inn']) && empty($rowData['pinfl'])) {
            return ['valid' => false, 'reason' => 'Missing both INN and PINFL'];
        }

        if (empty($rowData['contractNumber'])) {
            return ['valid' => false, 'reason' => 'Missing contract number'];
        }

        if ($rowData['contractAmount'] <= 0) {
            return ['valid' => false, 'reason' => 'Invalid contract amount: ' . $rowData['contractAmount']];
        }

        // Check for Excel errors
        if (stripos($rowData['inn'], '#REF') !== false ||
            stripos($rowData['companyName'], '#REF') !== false ||
            stripos($rowData['inn'], '#N/A') !== false) {
            return ['valid' => false, 'reason' => 'Contains Excel error'];
        }

        // Check for summary rows
        if (stripos($rowData['companyName'], 'ЖАМИ') !== false ||
            stripos($rowData['companyName'], 'ИТОГО') !== false ||
            stripos($rowData['serialNumber'], 'ЖАМИ') !== false) {
            return ['valid' => false, 'reason' => 'Summary row'];
        }

        return ['valid' => true, 'reason' => null];
    }

    private function createOrFindSubject($rowData, $rowNumber)
    {
        $inn = $this->cleanIdentifier($rowData['inn'], 9);
        $pinfl = $this->cleanIdentifier($rowData['pinfl'], 14);
        $companyName = $rowData['companyName'];

        if (empty($companyName)) {
            throw new \Exception("Company name is required");
        }

        $isLegalEntity = !empty($inn) || $this->isLegalEntityByName($companyName);

        // Handle the case where both INN and PINFL exist (use INN for legal entities)
        if ($isLegalEntity && !empty($inn)) {
            $searchField = 'inn';
            $searchValue = $inn;
        } elseif (!$isLegalEntity && !empty($pinfl)) {
            $searchField = 'pinfl';
            $searchValue = $pinfl;
        } else {
            // Generate identifier if missing
            $searchField = $isLegalEntity ? 'inn' : 'pinfl';
            $searchValue = $isLegalEntity ?
                'IMP' . str_pad($rowNumber, 6, '0', STR_PAD_LEFT) :
                'IND' . str_pad($rowNumber, 11, '0', STR_PAD_LEFT);
        }

        // Find or create subject
        $subject = Subject::where($searchField, $searchValue)->first();

        if (!$subject) {
            $subject = $this->createNewSubject($searchValue, $companyName, $isLegalEntity, $inn, $pinfl);
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

        $volume = $rowData['area'] > 0 ? $rowData['area'] :
                 ($rowData['contractAmount'] > 0 && $baseAmount->amount > 0 ?
                  ($rowData['contractAmount'] / $baseAmount->amount) : 1);

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
            return $districts->first(); // Fallback to first district
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

        // Log unmapped districts
        if (!$district) {
            $this->logUnmappedDistrict($districtName, $rowNumber);
            $district = $districts->first(); // Use fallback
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
        // Parse payment terms (e.g., "20/80", "40/60")
        $initialPaymentPercent = 20; // Default
        if (!empty($rowData['paymentTerms'])) {
            if (preg_match('/(\d+)\/(\d+)/', $rowData['paymentTerms'], $matches)) {
                $initialPaymentPercent = (int)$matches[1];
            } elseif (preg_match('/(\d+)/', $rowData['paymentTerms'], $matches)) {
                $percent = (int)$matches[1];
                $initialPaymentPercent = $percent > 100 ? 20 : $percent;
            }
        }

        // Override with advance percent if provided
        if ($rowData['advancePercent'] > 0) {
            $initialPaymentPercent = min(100, $rowData['advancePercent']);
        }

        $status = $this->getContractStatus($rowData['contractStatus'], $statuses);
        $isActive = !$this->isContractCancelled($rowData['contractStatus']);

        $contractDate = $rowData['contractDate'] ?: now();
        $constructionPeriodYears = $rowData['paymentPeriod'] > 0 ?
            max(1, ceil($rowData['paymentPeriod'] / 12)) : 1;
        $quartersCount = $rowData['paymentPeriod'] > 0 ?
            max(1, ceil($rowData['paymentPeriod'] / 3)) : 4;

        // Ensure unique contract number
        $contractNumber = $rowData['contractNumber'];
        if (empty($contractNumber)) {
            $contractNumber = 'APZ-' . now()->format('Y') . '-' . str_pad($rowNumber, 6, '0', STR_PAD_LEFT);
        }

        // Check for duplicate and make unique if necessary
        $originalNumber = $contractNumber;
        $counter = 1;
        while (Contract::where('contract_number', $contractNumber)->exists()) {
            $contractNumber = $originalNumber . '-' . $counter;
            $counter++;
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
            'coefficient' => $this->calculateCoefficient($rowData['contractAmount'], $object->construction_volume, $baseAmount->amount),
            'total_amount' => $rowData['contractAmount'],
            'payment_type' => $initialPaymentPercent >= 100 ? 'full' : 'installment',
            'initial_payment_percent' => $initialPaymentPercent,
            'construction_period_years' => $constructionPeriodYears,
            'quarters_count' => $quartersCount,
            'is_active' => $isActive,
        ]);
    }

    private function calculateCoefficient($contractAmount, $volume, $baseAmount)
    {
        if ($contractAmount > 0 && $volume > 0 && $baseAmount > 0) {
            return round($contractAmount / ($volume * $baseAmount), 2);
        }
        return 1.0;
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

    private function createPaymentSchedule($contract, $rowData, $rowNumber)
    {
        if ($contract->payment_type === 'full') {
            return; // No schedule needed for full payments
        }

        $remainingAmount = $contract->total_amount * (100 - $contract->initial_payment_percent) / 100;

        // Use best available payment amount
        if ($rowData['installmentPayment'] > 0) {
            $remainingAmount = $rowData['installmentPayment'];
        } elseif ($rowData['totalPayment'] > 0 && $rowData['totalPayment'] < $contract->total_amount) {
            $remainingAmount = $rowData['totalPayment'];
        }

        $quarterAmount = $rowData['monthlyPayment'] > 0 ?
            ($rowData['monthlyPayment'] * 3) :
            ($remainingAmount / max(1, $contract->quarters_count));

        $this->createPaymentScheduleRecords($contract, $quarterAmount);
    }

    private function createPaymentScheduleRecords($contract, $quarterAmount)
    {
        $startYear = $contract->contract_date->year;
        $startQuarter = ceil($contract->contract_date->month / 3);

        for ($i = 0; $i < $contract->quarters_count; $i++) {
            $year = $startYear + floor(($startQuarter + $i - 1) / 4);
            $quarter = (($startQuarter + $i - 1) % 4) + 1;

            PaymentSchedule::create([
                'contract_id' => $contract->id,
                'year' => $year,
                'quarter' => $quarter,
                'quarter_amount' => $quarterAmount,
                'is_active' => true,
            ]);
        }
    }

    private function createActualPayments($contract, $rowData, $rowNumber)
    {
        if ($rowData['totalActualPayment'] <= 0) {
            return;
        }

        try {
            if (!empty($rowData['monthlyPayments'])) {
                $this->createMonthlyActualPayments($contract, $rowData['monthlyPayments']);
            } else {
                $this->createSingleActualPayment($contract, $rowData['totalActualPayment']);
            }
        } catch (\Exception $e) {
            Log::error("Failed to create actual payment for row {$rowNumber}", [
                'contract_id' => $contract->id,
                'amount' => $rowData['totalActualPayment'],
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function createMonthlyActualPayments($contract, $monthlyPayments)
    {
        foreach ($monthlyPayments as $monthDate => $monthlyAmount) {
            if ($monthlyAmount > 0) {
                $paymentDate = $this->parseExcelDateFromString($monthDate) ?: $contract->contract_date;

                ActualPayment::create([
                    'contract_id' => $contract->id,
                    'payment_date' => $paymentDate,
                    'amount' => $monthlyAmount,
                    'year' => $paymentDate->year,
                    'quarter' => ceil($paymentDate->month / 3),
                    'payment_number' => $this->generatePaymentNumber($contract->id, $paymentDate),
                    'notes' => 'Импортировано из APZ файла - ' . $monthDate,
                ]);
            }
        }
    }

    private function createSingleActualPayment($contract, $amount)
    {
        ActualPayment::create([
            'contract_id' => $contract->id,
            'payment_date' => $contract->contract_date,
            'amount' => $amount,
            'year' => $contract->contract_date->year,
            'quarter' => ceil($contract->contract_date->month / 3),
            'payment_number' => $this->generatePaymentNumber($contract->id, $contract->contract_date),
            'notes' => 'Импортировано из APZ файла',
        ]);
    }

    private function generatePaymentNumber($contractId, $date)
    {
        return 'APZ-' . $contractId . '-' . $date->format('Y-m') . '-' . now()->timestamp;
    }

    private function createCouncilConclusion($object, $rowData, $rowNumber)
    {
        $conclusionText = trim($rowData['councilConclusion1'] . ' ' . $rowData['councilConclusion2']);

        if (empty($conclusionText)) {
            return;
        }

        $status = 'pending';
        if (stripos($conclusionText, 'да') !== false || stripos($conclusionText, 'Да') !== false) {
            $status = 'approved';
        } elseif (stripos($conclusionText, 'нет') !== false || stripos($conclusionText, 'Нет') !== false) {
            $status = 'rejected';
        }

        try {
            // Check if CouncilConclusion model has notes field
            $schema = DB::connection()->getSchemaBuilder();
            $hasNotesField = $schema->hasColumn('council_conclusions', 'notes');

            $conclusionData = [
                'object_id' => $object->id,
                'application_date' => $object->application_date,
                'conclusion_date' => now(),
                'status' => $status,
            ];

            // Only add notes if the field exists
            if ($hasNotesField) {
                $conclusionData['notes'] = 'Импортировано из APZ: ' . $conclusionText;
            }

            CouncilConclusion::create($conclusionData);
        } catch (\Exception $e) {
            Log::error("Failed to create council conclusion for row {$rowNumber}", [
                'object_id' => $object->id,
                'error' => $e->getMessage()
            ]);
            // Don't throw - this is optional data
        }
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
                'district' => $row[$this->columnMap['district']] ?? '',
                'amount' => $row[$this->columnMap['contract_amount']] ?? ''
            ]
        ];
    }

    private function showImportSummary($processedCount, $skippedCount, $errorCount)
    {
        $this->command->info("\n" . str_repeat("=", 80));
        $this->command->info("APZ DATA IMPORT SUMMARY");
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

    private function isEmptyRow($row)
    {
        if (empty($row)) return true;

        $nonEmptyCount = 0;
        foreach ($row as $cell) {
            if (trim($cell) !== '') {
                $nonEmptyCount++;
                if ($nonEmptyCount > 2) {
                    return false;
                }
            }
        }
        return $nonEmptyCount <= 2;
    }

    private function isSummaryRow($row)
    {
        foreach (array_slice($row, 0, 10) as $cell) {
            if (stripos($cell, 'ЖАМИ') !== false ||
                stripos($cell, 'ИТОГО') !== false ||
                stripos($cell, 'TOTAL') !== false) {
                return true;
            }
        }
        return false;
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

        // Fill missing columns with empty strings (extend to at least 50 columns to handle all data)
        for ($i = 0; $i <= max(50, $maxCol); $i++) {
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

    private function parseExcelSerialDate($serialNumber)
    {
        try {
            if (!is_numeric($serialNumber) || $serialNumber <= 0) {
                return null;
            }

            // Excel epoch starts at January 1, 1900
            // But Excel incorrectly treats 1900 as a leap year, so we subtract 2 days
            $excelEpoch = Carbon::create(1900, 1, 1);
            return $excelEpoch->addDays((int)$serialNumber - 2);
        } catch (\Exception $e) {
            Log::warning("Failed to parse Excel serial date: {$serialNumber}");
            return null;
        }
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

    private function parseExcelDateFromString($dateString)
    {
        try {
            // Handle M/D/YYYY format (4/30/2024)
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateString, $matches)) {
                return Carbon::createFromFormat('n/j/Y', $dateString);
            }

            // Handle DD.MM.YYYY format (30.04.2024)
            if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $dateString, $matches)) {
                return Carbon::createFromFormat('d.m.Y', $dateString);
            }

            // Try other common formats
            $formats = ['n/j/Y', 'd.m.Y', 'd/m/Y', 'Y-m-d', 'm/d/Y'];
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $dateString);
                } catch (\Exception $e) {
                    continue;
                }
            }

            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            Log::warning("Failed to parse date string: {$dateString}");
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

    private function parsePercent($value)
    {
        if (empty($value) || $value === '-') return 0;

        // If it's already a decimal (like 0.20 for 20%), convert to percentage
        if (is_numeric($value) && $value <= 1 && $value > 0) {
            return $value * 100;
        }

        // Remove % sign and other characters
        $cleaned = str_replace(['%', ',', ' '], ['', '.', ''], (string)$value);
        $cleaned = preg_replace('/[^\d.-]/', '', $cleaned);

        return (float)$cleaned;
    }
}

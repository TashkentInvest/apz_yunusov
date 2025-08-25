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

    public function run()
    {
        $filePath = public_path('apz_new_siroj.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error('APZ data file not found: ' . $filePath);
            return;
        }

        $this->command->info('Starting APZ data import from XLSX...');
        Log::info('Starting APZ data import from XLSX');

        try {
            $this->validateEssentialData();
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

    private function validateEssentialData()
    {
        $this->command->info('Checking essential data...');
        $districtCount = District::where('is_active', true)->count();
        $statusCount = ContractStatus::where('is_active', true)->count();
        $orgFormCount = \App\Models\OrgForm::where('is_active', true)->count();

        $this->command->info("Districts: {$districtCount}");
        $this->command->info("Statuses: {$statusCount}");
        $this->command->info("Org Forms: {$orgFormCount}");

        if ($districtCount === 0 || $statusCount === 0) {
            throw new \Exception('Essential data missing! Run: php artisan db:seed --class=EssentialDataSeeder');
        }
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
        // Find header row
        $headerRowIndex = $this->findHeaderRow($data);

        if ($headerRowIndex === -1) {
            throw new \Exception('Could not find header row in Excel file');
        }

        $this->headerRow = $this->buildCompleteHeader($data, $headerRowIndex);
        $this->analyzeHeaderStructure($this->headerRow);

        // Remove header rows and return data rows
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
        for ($i = 0; $i < min(10, count($data)); $i++) {
            $score = $this->calculateHeaderScore($data[$i]);
            if ($score >= 5) {
                return $i;
            }
        }
        return -1;
    }

    private function calculateHeaderScore($row)
    {
        $requiredColumns = ['ИНН', 'ПИНФЛ', 'Корхона номи', 'шарт', 'Контракт', 'ФАКТ', '№', 'Туман', 'қиймати'];
        $score = 0;

        foreach ($row as $cell) {
            $cellText = trim($cell);
            foreach ($requiredColumns as $required) {
                if (stripos($cellText, $required) !== false) {
                    $score++;
                    break;
                }
            }
        }

        return $score;
    }

    private function buildCompleteHeader($allData, $startRowIndex)
    {
        $baseHeader = $allData[$startRowIndex];

        // Expected column structure based on your Excel file
        $expectedColumns = [
            'Умуман тўловни амалга оширмаганлар', '', 'Қарздорлар', '№', 'ИНН', 'ПИНФЛ', 'Корхона номи', 'шарт. №',
            'Контракт ҳолати', 'шартнома санаси', 'Якунлаш сана', 'Тўлов шарти', 'Тўлов муддати', 'Аванс', 'Туман',
            'М3 (площадь)', 'Шартнома қиймати', 'Бўнак тўлов', 'Ойлик тўлов', 'Жами тўлов', 'Қолдиқ', 'ФАКТ'
        ];

        // Extend base header if needed
        $completeHeader = $baseHeader;
        for ($i = count($completeHeader); $i < count($expectedColumns); $i++) {
            $completeHeader[] = $expectedColumns[$i];
        }

        // Look for additional date columns in subsequent rows
        for ($nextRow = $startRowIndex + 1; $nextRow < min($startRowIndex + 3, count($allData)); $nextRow++) {
            $row = $allData[$nextRow];
            foreach ($row as $index => $cell) {
                if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', trim($cell))) {
                    // Extend header to include date columns
                    while (count($completeHeader) <= $index) {
                        $completeHeader[] = '';
                    }
                    $completeHeader[$index] = trim($cell);
                }
            }
        }

        return $completeHeader;
    }

    private function analyzeHeaderStructure($headerRow)
    {
        $this->monthlyPaymentColumns = [];

        foreach ($headerRow as $index => $header) {
            $headerText = trim($header);

            // Detect date columns (monthly payment columns)
            if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $headerText)) {
                $this->monthlyPaymentColumns[$index] = [
                    'date' => $headerText,
                    'parsed_date' => $this->parseExcelDateFromString($headerText)
                ];
            }
        }

        Log::info("Monthly payment columns detected", [
            'count' => count($this->monthlyPaymentColumns),
            'sample_dates' => array_slice(array_column($this->monthlyPaymentColumns, 'date'), 0, 5)
        ]);
    }

    private function getColumnMapping()
    {
        $map = [];

        foreach ($this->headerRow as $index => $header) {
            $headerText = strtolower(trim($header));

            // Map columns based on keywords - only set if not already mapped
            if (stripos($headerText, 'инн') !== false && !isset($map['inn'])) {
                $map['inn'] = $index;
            } elseif (stripos($headerText, 'пинфл') !== false && !isset($map['pinfl'])) {
                $map['pinfl'] = $index;
            } elseif (stripos($headerText, 'корхона') !== false && stripos($headerText, 'номи') !== false) {
                $map['company_name'] = $index;
            } elseif (stripos($headerText, 'шарт') !== false && stripos($headerText, '№') !== false) {
                $map['contract_number'] = $index;
            } elseif (stripos($headerText, 'контракт') !== false && stripos($headerText, 'ҳолати') !== false) {
                $map['contract_status'] = $index;
            } elseif (stripos($headerText, 'шартнома') !== false && stripos($headerText, 'санаси') !== false) {
                $map['contract_date'] = $index;
            } elseif (stripos($headerText, 'якунлаш') !== false && stripos($headerText, 'сана') !== false) {
                $map['completion_date'] = $index;
            } elseif (stripos($headerText, 'тўлов') !== false && stripos($headerText, 'шарти') !== false) {
                $map['payment_terms'] = $index;
            } elseif (stripos($headerText, 'тўлов') !== false && stripos($headerText, 'муддати') !== false) {
                $map['payment_period'] = $index;
            } elseif (stripos($headerText, 'аванс') !== false) {
                $map['advance_percent'] = $index;
            } elseif (stripos($headerText, 'туман') !== false) {
                $map['district'] = $index;
            } elseif (stripos($headerText, 'м3') !== false || stripos($headerText, 'площадь') !== false) {
                $map['area'] = $index;
            } elseif (stripos($headerText, 'шартнома') !== false && stripos($headerText, 'қиймати') !== false) {
                $map['contract_amount'] = $index;
            } elseif (stripos($headerText, 'бўнак') !== false && stripos($headerText, 'тўлов') !== false) {
                $map['installment_payment'] = $index;
            } elseif (stripos($headerText, 'ойлик') !== false && stripos($headerText, 'тўлов') !== false) {
                $map['monthly_payment'] = $index;
            } elseif (stripos($headerText, 'жами') !== false && stripos($headerText, 'тўлов') !== false && !stripos($headerText, 'факт')) {
                $map['total_payment'] = $index;
            } elseif (stripos($headerText, 'қолдиқ') !== false) {
                $map['remaining'] = $index;
            } elseif (stripos($headerText, 'факт') !== false && strlen($headerText) < 10) {
                $map['actual_payment_indicator'] = $index;
            } elseif (trim($headerText) === '№' || (stripos($headerText, '№') !== false && strlen($headerText) < 5)) {
                $map['serial_number'] = $index;
            }
        }

        // Set default fallbacks based on typical Excel structure
        $this->setDefaultColumnMappings($map);

        Log::debug("Column mapping created", [
            'mapped_columns' => count($map),
            'sample_mapping' => array_slice($map, 0, 10, true)
        ]);

        return $map;
    }

    private function setDefaultColumnMappings(&$map)
    {
        $defaults = [
            'council_conclusion_1' => 0,
            'council_conclusion_2' => 2,
            'serial_number' => 3,
            'inn' => 4,
            'pinfl' => 5,
            'company_name' => 6,
            'contract_number' => 7,
            'contract_status' => 8,
            'contract_date' => 9,
            'completion_date' => 10,
            'payment_terms' => 11,
            'payment_period' => 12,
            'advance_percent' => 13,
            'district' => 14,
            'area' => 15,
            'contract_amount' => 16,
            'installment_payment' => 17,
            'monthly_payment' => 18,
            'total_payment' => 19,
            'remaining' => 20,
            'actual_payment_indicator' => 21,
        ];

        foreach ($defaults as $key => $defaultIndex) {
            if (!isset($map[$key])) {
                $map[$key] = $defaultIndex;
            }
        }
    }

    private function processDataRows($dataRows, $districts, $statuses, $baseAmount)
    {
        DB::beginTransaction();

        $processedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 1;

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
        $columnMap = $this->getColumnMapping();
        $rowData = $this->extractRowData($row, $columnMap);

        // Debug logging for first few rows
        if ($rowNumber <= 10) {
            Log::info("Row {$rowNumber} analysis", [
                'company_name' => $rowData['companyName'],
                'contract_amount' => $rowData['contractAmount'],
                'monthly_payments_count' => count($rowData['monthlyPayments'])
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

    private function extractRowData($row, $columnMap)
    {
        // Extract monthly payments
        $monthlyPayments = [];
        foreach ($this->monthlyPaymentColumns as $colIndex => $monthInfo) {
            $payment = $this->parseAmount($row[$colIndex] ?? 0);
            if ($payment > 0) {
                $monthlyPayments[$monthInfo['date']] = $payment;
            }
        }

        // Calculate total actual payment
        $totalActualPayment = array_sum($monthlyPayments);
        if ($totalActualPayment <= 0) {
            $actualPaymentRaw = $row[$columnMap['actual_payment_indicator']] ?? 0;
            $contractAmount = $this->parseAmount($row[$columnMap['contract_amount']] ?? 0);
            $totalPayment = $this->parseAmount($row[$columnMap['total_payment']] ?? 0);
            $totalActualPayment = $this->parseActualPayment($actualPaymentRaw, $contractAmount, $totalPayment);
        }

        return [
            'councilConclusion1' => $this->cleanString($row[$columnMap['council_conclusion_1']] ?? ''),
            'councilConclusion2' => $this->cleanString($row[$columnMap['council_conclusion_2']] ?? ''),
            'serialNumber' => $this->cleanString($row[$columnMap['serial_number']] ?? ''),
            'inn' => $this->cleanString($row[$columnMap['inn']] ?? ''),
            'pinfl' => $this->cleanString($row[$columnMap['pinfl']] ?? ''),
            'companyName' => $this->cleanString($row[$columnMap['company_name']] ?? ''),
            'contractNumber' => $this->cleanString($row[$columnMap['contract_number']] ?? ''),
            'contractStatus' => $this->cleanString($row[$columnMap['contract_status']] ?? ''),
            'contractDate' => $this->parseExcelDate($row[$columnMap['contract_date']] ?? ''),
            'completionDate' => $this->parseExcelDate($row[$columnMap['completion_date']] ?? ''),
            'paymentTerms' => $this->cleanString($row[$columnMap['payment_terms']] ?? ''),
            'paymentPeriod' => (int)($row[$columnMap['payment_period']] ?? 0),
            'advancePercent' => $this->parsePercent($row[$columnMap['advance_percent']] ?? ''),
            'districtName' => $this->cleanString($row[$columnMap['district']] ?? ''),
            'area' => $this->parseAmount($row[$columnMap['area']] ?? 0),
            'contractAmount' => $this->parseAmount($row[$columnMap['contract_amount']] ?? 0),
            'installmentPayment' => $this->parseAmount($row[$columnMap['installment_payment']] ?? 0),
            'monthlyPayment' => $this->parseAmount($row[$columnMap['monthly_payment']] ?? 0),
            'totalPayment' => $this->parseAmount($row[$columnMap['total_payment']] ?? 0),
            'remaining' => $this->parseAmount($row[$columnMap['remaining']] ?? 0),
            'monthlyPayments' => $monthlyPayments,
            'totalActualPayment' => $totalActualPayment
        ];
    }

    private function validateRowData($rowNumber, $rowData)
    {
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
            return ['valid' => false, 'reason' => 'Invalid contract amount'];
        }

        // Check for Excel errors
        if (stripos($rowData['inn'], '#REF') !== false || stripos($rowData['companyName'], '#REF') !== false) {
            return ['valid' => false, 'reason' => 'Contains Excel error'];
        }

        // Check for summary rows
        if (stripos($rowData['companyName'], 'ЖАМИ') !== false || stripos($rowData['companyName'], 'ИТОГО') !== false) {
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
        $searchField = $isLegalEntity ? 'inn' : 'pinfl';
        $searchValue = $isLegalEntity ? $inn : $pinfl;

        // Generate identifier if missing
        if (empty($searchValue)) {
            $searchValue = $isLegalEntity ?
                'IMP' . str_pad($rowNumber, 6, '0', STR_PAD_LEFT) :
                'IND' . str_pad($rowNumber, 11, '0', STR_PAD_LEFT);
        }

        // Find or create subject
        $subject = Subject::where($searchField, $searchValue)->first();

        if (!$subject) {
            $subject = $this->createNewSubject($searchValue, $companyName, $isLegalEntity);
        }

        return $subject;
    }

    private function createNewSubject($identifier, $companyName, $isLegalEntity)
    {
        $subjectData = [
            'is_legal_entity' => $isLegalEntity,
            'is_active' => true,
            'country_code' => 'UZ',
            'is_resident' => true,
            'company_name' => $companyName,
        ];

        if ($isLegalEntity) {
            $subjectData['inn'] = $identifier;
            $subjectData['org_form_id'] = $this->ensureOrgFormExists();
        } else {
            $subjectData['pinfl'] = $identifier;
            $subjectData['document_type'] = 'Паспорт';
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
            'address' => 'APZ импорт - Адрес не указан',
            'construction_volume' => $volume,
            'is_active' => true,
            'application_date' => now(),
        ]);
    }

    private function findDistrict($districtName, $districts, $rowNumber)
    {
        if (empty($districtName)) {
            return $districts->first(); // Fallback to first district
        }

        // Try exact match
        $district = $districts->where('name_ru', $districtName)->first();

        // Try case-insensitive match
        if (!$district) {
            $district = $districts->filter(function($d) use ($districtName) {
                return strcasecmp($d->name_ru, $districtName) === 0;
            })->first();
        }

        // Try partial match
        if (!$district) {
            $district = $districts->filter(function($d) use ($districtName) {
                return stripos($d->name_ru, $districtName) !== false ||
                       stripos($districtName, $d->name_ru) !== false;
            })->first();
        }

        // Try district mappings
        if (!$district) {
            $district = $this->findDistrictByMapping($districtName, $districts);
        }

        // Log unmapped districts
        if (!$district) {
            $this->logUnmappedDistrict($districtName, $rowNumber);
            $district = $districts->first(); // Use fallback
        }

        return $district;
    }

    private function findDistrictByMapping($districtName, $districts)
    {
        $districtMappings = [
            'Алмазарский' => ['Олмазор', 'Olmazar', 'Алмазарский', 'Алмазар'],
            'Мирзо-Улугбекский' => ['Мирзо-Улуғбек', 'Мирзо-Улугбек', 'Mirzo-Ulugbek'],
            'Яккасарайский' => ['Яккасарой', 'Яккасарай', 'Yakkasaray'],
            'Шайхантахурский' => ['Шайхонтохур', 'Шайхантаур', 'Shayhantahur'],
            // Add more mappings as needed
        ];

        foreach ($districtMappings as $standardName => $variations) {
            foreach ($variations as $variation) {
                if (stripos($districtName, $variation) !== false) {
                    return $districts->where('name_ru', $standardName)->first();
                }
            }
        }

        return null;
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
        // Parse payment terms
        $initialPaymentPercent = 20; // Default
        if (!empty($rowData['paymentTerms'])) {
            if (preg_match('/(\d+)/', $rowData['paymentTerms'], $matches)) {
                $initialPaymentPercent = (int)$matches[1];
            }
        }

        if ($rowData['advancePercent'] > 0) {
            $initialPaymentPercent = $rowData['advancePercent'];
        }

        $status = $this->getContractStatus($rowData['contractStatus'], $statuses);
        $isActive = !$this->isContractCancelled($rowData['contractStatus']);

        $contractDate = $rowData['contractDate'] ?: now();
        $constructionPeriodYears = $rowData['paymentPeriod'] > 0 ? max(1, ceil($rowData['paymentPeriod'] / 12)) : 1;
        $quartersCount = max(1, ceil($rowData['paymentPeriod'] / 3));

        // Ensure unique contract number
        $contractNumber = $rowData['contractNumber'];
        if (empty($contractNumber)) {
            $contractNumber = 'APZ-' . now()->format('Y') . '-' . str_pad($rowNumber, 6, '0', STR_PAD_LEFT);
        }

        if (Contract::where('contract_number', $contractNumber)->exists()) {
            $contractNumber .= '-' . $rowNumber;
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
            return $contractAmount / ($volume * $baseAmount);
        }
        return 1.0;
    }

    private function getContractStatus($contractStatus, $statuses)
    {
        if (!empty($contractStatus)) {
            // Check for cancelled status
            if (stripos($contractStatus, 'Бекор') !== false) {
                $cancelled = $statuses->where('name_ru', 'LIKE', '%Бекор%')->first();
                if ($cancelled) return $cancelled;
            }
            // Check for completed status
            elseif (stripos($contractStatus, 'завершен') !== false ||
                   stripos($contractStatus, 'якун') !== false ||
                   stripos($contractStatus, 'tugal') !== false) {
                $completed = $statuses->where('name_ru', 'LIKE', '%Якун%')->first();
                if ($completed) return $completed;
            }
        }

        // Default to active status
        return $statuses->where('name_ru', 'LIKE', '%Действ%')->first() ?:
               $statuses->where('name_ru', 'LIKE', '%Амал%')->first() ?:
               $statuses->first();
    }

    private function isContractCancelled($contractStatus)
    {
        return !empty($contractStatus) && (
            stripos($contractStatus, 'Бекор') !== false ||
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
        if (stripos($conclusionText, 'да') !== false) {
            $status = 'approved';
        } elseif (stripos($conclusionText, 'нет') !== false) {
            $status = 'rejected';
        }

        try {
            CouncilConclusion::create([
                'object_id' => $object->id,
                'application_date' => now(),
                'conclusion_date' => now(),
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create council conclusion for row {$rowNumber}", [
                'object_id' => $object->id,
                'error' => $e->getMessage()
            ]);
            // Don't throw - this is optional data
        }
    }

    private function parseActualPayment($rawValue, $contractAmount, $totalPayment)
    {
        if (empty($rawValue) || $rawValue === '-' || $rawValue === 0) {
            return 0;
        }

        $numericValue = $this->parseAmount($rawValue);

        // If value is between 0 and 1, treat as percentage
        if ($numericValue > 0 && $numericValue <= 1) {
            $baseAmount = $totalPayment > 0 ? $totalPayment : $contractAmount;
            return $baseAmount * $numericValue;
        }

        return $numericValue > 1 ? $numericValue : 0;
    }

    // Utility methods for data validation and cleaning
    private function cleanIdentifier($value, $maxLength)
    {
        if (empty($value)) return '';

        // Handle scientific notation
        if (is_numeric($value) && (stripos($value, 'E+') !== false || stripos($value, 'E-') !== false)) {
            $value = number_format((float)$value, 0, '', '');
        }

        $cleaned = preg_replace('/[^\w]/', '', (string)$value);
        return substr($cleaned, 0, $maxLength);
    }

    private function isLegalEntityByName($companyName)
    {
        $legalPatterns = ['OOO', 'МЧЖ', 'MCHJ', 'ООО', 'ТОО', 'LLC', 'АЖ', 'СП', 'ТИФ', 'Ltd', 'Inc'];

        foreach ($legalPatterns as $pattern) {
            if (stripos($companyName, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    private function ensureOrgFormExists()
    {
        $orgForm = \App\Models\OrgForm::where('is_active', true)->first();

        if (!$orgForm) {
            $orgForm = \App\Models\OrgForm::create([
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
                'company_name' => $row[6] ?? '',
                'contract_number' => $row[7] ?? '',
                'inn' => $row[4] ?? '',
                'district' => $row[14] ?? '',
                'amount' => $row[16] ?? ''
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
        $districts = District::where('is_active', true)->get();

        if ($districts->isEmpty()) {
            throw new \Exception("No districts found in database. Please run EssentialDataSeeder first.");
        }

        return $districts;
    }

    private function getContractStatuses()
    {
        $statuses = ContractStatus::where('is_active', true)->get();

        if ($statuses->isEmpty()) {
            throw new \Exception("No contract statuses found in database. Please run EssentialDataSeeder first.");
        }

        return $statuses;
    }

    private function getOrCreateBaseAmount()
    {
        $baseAmount = BaseCalculationAmount::where('is_current', true)->first();

        if (!$baseAmount) {
            $baseAmount = BaseCalculationAmount::create([
                'amount' => 412000.00,
                'effective_from' => '2024-01-01',
                'is_current' => true
            ]);
            Log::info("Created default base amount", ['amount' => $baseAmount->amount]);
        }

        return $baseAmount;
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

        // Fill missing columns with empty strings
        for ($i = 0; $i <= max(45, $maxCol); $i++) {
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
            if (is_numeric($value) && $value > 1) {
                $excelEpoch = Carbon::create(1900, 1, 1);
                return $excelEpoch->addDays((int)$value - 2);
            }

            // Handle date strings
            if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $value)) {
                return Carbon::createFromFormat('d.m.Y', $value);
            }

            // Try common date formats
            $formats = ['d.m.Y', 'Y-m-d', 'd/m/Y', 'm/d/Y', 'Y/m/d'];
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
            // Handle dates like '30.09.2024'
            if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $dateString, $matches)) {
                return Carbon::createFromFormat('d.m.Y', $dateString);
            }

            // Try other common formats
            $formats = ['d.m.Y', 'd/m/Y', 'Y-m-d', 'm/d/Y'];
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

        // Handle scientific notation
        if (is_numeric($value)) {
            return (float)$value;
        }

        // Handle string representations
        if (is_string($value) && (stripos($value, 'E+') !== false || stripos($value, 'E-') !== false)) {
            return (float)$value;
        }

        // Clean and convert to float
        $cleaned = str_replace([',', ' '], ['.', ''], (string)$value);
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

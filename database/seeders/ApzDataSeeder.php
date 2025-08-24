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
    private $unmappedDistrictRows = []; // Track which rows had unmapped districts
    
    public function run()
    {
        $filePath = public_path('apz_data —test.xlsx');
        
        if (!file_exists($filePath)) {
            $this->command->error('APZ data file not found: ' . $filePath);
            return;
        }

        $this->command->info('Starting APZ data import from XLSX...');
        Log::info('Starting APZ data import from XLSX');

        try {
            // Check if essential data exists
            $this->command->info('Checking essential data...');
            $districtCount = District::where('is_active', true)->count();
            $statusCount = ContractStatus::where('is_active', true)->count();
            $orgFormCount = \App\Models\OrgForm::where('is_active', true)->count();
            
            $this->command->info("Districts: {$districtCount}");
            $this->command->info("Statuses: {$statusCount}");
            $this->command->info("Org Forms: {$orgFormCount}");
            
            Log::info("Essential data check", ['districts' => $districtCount, 'statuses' => $statusCount, 'org_forms' => $orgFormCount]);
            
            if ($districtCount === 0 || $statusCount === 0) {
                $this->command->error('Essential data missing! Run: php artisan db:seed --class=EssentialDataSeeder');
                return;
            }

            $data = $this->readXlsx($filePath);
            
            if (empty($data)) {
                $this->command->error('No data found in XLSX file');
                return;
            }

            // Remove header row
            if (count($data) > 0 && $this->isHeaderRow($data[0])) {
                array_shift($data);
                Log::info('Header row removed');
            }

            // Get required reference data
            $districts = $this->getDistricts();
            $statuses = $this->getContractStatuses();
            $baseAmount = $this->getOrCreateBaseAmount();
            
            Log::info("Reference data loaded", ['districts' => $districts->count(), 'statuses' => $statuses->count()]);

            DB::beginTransaction();
            
            $processedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;
            
            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // +2 because we removed header row and Excel starts from 1
                
                if ($this->isEmptyRow($row) || $this->isSummaryRow($row)) {
                    $skippedCount++;
                    $this->logSkippedRow($rowNumber, 'Empty or summary row', $row);
                    continue;
                }

                try {
                    $result = $this->processApzRow($row, $districts, $statuses, $baseAmount, $rowNumber);
                    if ($result) {
                        $processedCount++;
                        
                        if ($processedCount % 50 == 0) {
                            $this->command->info("Processed {$processedCount} records...");
                        }
                    } else {
                        $skippedCount++;
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $errorMessage = "Error processing row {$rowNumber}: " . $e->getMessage();
                    $this->command->warn($errorMessage);
                    
                    Log::error($errorMessage, [
                        'row_number' => $rowNumber,
                        'row_data' => array_slice($row, 0, 10), // Log first 10 columns only
                        'exception' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                    
                    if ($errorCount <= 3) {
                        $this->command->error("Details: " . $e->getFile() . ':' . $e->getLine());
                    }
                    continue;
                }
            }

            DB::commit();
            
            // Show final results
            $this->showImportSummary($processedCount, $skippedCount, $errorCount);
            
        } catch (\Exception $e) {
            DB::rollback();
            $errorMessage = 'Import failed: ' . $e->getMessage();
            $this->command->error($errorMessage);
            Log::error($errorMessage, ['exception' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    private function processApzRow($row, $districts, $statuses, $baseAmount, $rowNumber)
    {
        // Parse row data
        $councilConclusion1 = $this->cleanString($row[0] ?? '');
        $councilConclusion2 = $this->cleanString($row[1] ?? '');
        $rowId = $this->cleanString($row[2] ?? '');
        $inn = $this->cleanString($row[3] ?? '');
        $pinfl = $this->cleanString($row[4] ?? '');
        $companyName = $this->cleanString($row[5] ?? '');
        $contractNumber = $this->cleanString($row[6] ?? '');
        $contractStatus = $this->cleanString($row[7] ?? '');
        $contractDate = $this->parseExcelDate($row[8] ?? '');
        $completionDate = $this->parseExcelDate($row[9] ?? '');
        $paymentTerms = $this->cleanString($row[10] ?? '');
        $paymentPeriod = (int)($row[11] ?? 0);
        $advancePercent = $this->parsePercent($row[12] ?? '');
        $districtName = $this->cleanString($row[13] ?? '');
        $area = $this->parseAmount($row[14] ?? 0);
        $contractAmount = $this->parseAmount($row[15] ?? 0);
        $scheduledPayment = $this->parseAmount($row[16] ?? 0);
        $monthlyPayment = $this->parseAmount($row[17] ?? 0);
        $totalPayment = $this->parseAmount($row[18] ?? 0);
        $remaining = $this->parseAmount($row[19] ?? 0);
        $actualPaymentRaw = $row[20] ?? 0;
        
        // Validate essential data
        $validationResult = $this->validateRowData($rowNumber, $inn, $pinfl, $companyName, $contractNumber, $districtName, $contractAmount);
        if (!$validationResult['valid']) {
            $this->logSkippedRow($rowNumber, $validationResult['reason'], $row);
            return false;
        }
        
        // Parse actual payment
        $actualPayment = $this->parseActualPayment($actualPaymentRaw, $contractAmount, $totalPayment);
        
        Log::debug("Processing row {$rowNumber}", [
            'company_name' => $companyName,
            'contract_number' => $contractNumber,
            'district_name' => $districtName,
            'contract_amount' => $contractAmount
        ]);

        try {
            // 1. Create or find Subject
            $subject = $this->createOrFindSubject($inn, $pinfl, $companyName, $rowNumber);
            if (!$subject) {
                throw new \Exception("Failed to create or find subject");
            }
            
            // 2. Create Object (with proper district validation)
            $object = $this->createObject($subject, $districtName, $districts, $contractAmount, $baseAmount, $area, $rowNumber);
            if (!$object) {
                throw new \Exception("Failed to create object");
            }
            
            // 3. Create Contract
            $contract = $this->createContract(
                $subject, 
                $object, 
                $contractNumber, 
                $contractStatus, 
                $contractDate, 
                $completionDate,
                $contractAmount,
                $paymentTerms,
                $paymentPeriod,
                $advancePercent,
                $baseAmount,
                $statuses,
                $rowNumber
            );
            if (!$contract) {
                throw new \Exception("Failed to create contract");
            }

            // 4. Create Payment Schedule
            $this->createPaymentSchedule($contract, $monthlyPayment, $totalPayment, $rowNumber);

            // 5. Create Actual Payments (if any)
            if ($actualPayment > 0) {
                $this->createActualPayments($contract, $actualPayment, $rowNumber);
            }

            // 6. Create Council Conclusion (if exists)
            if ($councilConclusion1 || $councilConclusion2) {
                $this->createCouncilConclusion($object, $councilConclusion1, $councilConclusion2, $rowNumber);
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to process row {$rowNumber}", [
                'company_name' => $companyName,
                'contract_number' => $contractNumber,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function validateRowData($rowNumber, $inn, $pinfl, $companyName, $contractNumber, $districtName, $contractAmount)
    {
        // Check for essential data
        if (empty($companyName)) {
            return ['valid' => false, 'reason' => 'Missing company name'];
        }

        if (empty($inn) && empty($pinfl)) {
            return ['valid' => false, 'reason' => 'Missing both INN and PINFL'];
        }

        if (empty($contractNumber)) {
            return ['valid' => false, 'reason' => 'Missing contract number'];
        }

        if ($contractAmount <= 0) {
            return ['valid' => false, 'reason' => 'Invalid contract amount'];
        }

        // Check for #REF! errors
        if (stripos($inn, '#REF') !== false || stripos($companyName, '#REF') !== false) {
            return ['valid' => false, 'reason' => 'Contains #REF error'];
        }

        // Check for summary rows
        if (stripos($companyName, 'ЖАМИ') !== false) {
            return ['valid' => false, 'reason' => 'Summary row'];
        }

        return ['valid' => true, 'reason' => null];
    }

    private function createObject($subject, $districtName, $districts, $contractAmount, $baseAmount, $area = null, $rowNumber = null)
    {
        // PRODUCTION: Only use exact district matches from database
        $district = $districts->where('name_ru', $districtName)->first();
        
        // If district not found, try fuzzy matching
        if (!$district && !empty($districtName)) {
            $district = $districts->filter(function($d) use ($districtName) {
                // Try exact name matches with case insensitive
                return strcasecmp($d->name_ru, $districtName) === 0 ||
                       strcasecmp($d->name_uz, $districtName) === 0;
            })->first();
        }
        
        // If still not found, try partial matching
        if (!$district && !empty($districtName)) {
            $district = $districts->filter(function($d) use ($districtName) {
                return stripos($d->name_ru, $districtName) !== false || 
                       stripos($districtName, $d->name_ru) !== false;
            })->first();
        }
        
        // Log unmapped districts for review
        if (!$district) {
            if (!isset($this->unmappedDistricts[$districtName])) {
                $this->unmappedDistricts[$districtName] = [];
            }
            $this->unmappedDistricts[$districtName][] = $rowNumber;
            
            Log::warning("Unmapped district found", [
                'district_name' => $districtName,
                'row_number' => $rowNumber,
                'available_districts' => $districts->pluck('name_ru')->toArray()
            ]);
            
            // Use first available district as fallback, but log it
            $district = $districts->first();
            Log::info("Using fallback district", [
                'requested' => $districtName,
                'fallback' => $district->name_ru,
                'row_number' => $rowNumber
            ]);
        }
        
        if (!$district) {
            throw new \Exception("No districts available in database");
        }

        // Calculate volume
        $volume = $area > 0 ? $area : 
                 ($contractAmount > 0 ? ($contractAmount / $baseAmount->amount) : 1);

        try {
            $object = Objectt::create([
                'subject_id' => $subject->id,
                'district_id' => $district->id,
                'address' => 'APZ импорт - Адрес не указан',
                'construction_volume' => $volume,
                'is_active' => true,
                'application_date' => now(),
            ]);
            
            return $object;
        } catch (\Exception $e) {
            Log::error("Failed to create object", [
                'row_number' => $rowNumber,
                'subject_id' => $subject->id,
                'district_id' => $district->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function createOrFindSubject($inn, $pinfl, $companyName, $rowNumber)
    {
        // Clean and validate data
        $inn = $this->cleanIdentifier($inn, 9);
        $pinfl = $this->cleanIdentifier($pinfl, 14);
        $companyName = trim($companyName);
        
        if (empty($companyName)) {
            throw new \Exception("Company name is required");
        }
        
        // Determine entity type
        $isLegalEntity = !empty($inn) || $this->isLegalEntityByName($companyName);
        
        $searchField = $isLegalEntity ? 'inn' : 'pinfl';
        $searchValue = $isLegalEntity ? $inn : $pinfl;
        
        // Generate unique identifier if empty (production-safe)
        if (empty($searchValue)) {
            if ($isLegalEntity) {
                $searchValue = 'IMP' . str_pad($rowNumber, 6, '0', STR_PAD_LEFT); // IMP000001
            } else {
                $searchValue = 'IND' . str_pad($rowNumber, 11, '0', STR_PAD_LEFT); // IND00000000001
            }
            
            Log::info("Generated identifier for import", [
                'row_number' => $rowNumber,
                'company_name' => $companyName,
                'generated_id' => $searchValue,
                'type' => $isLegalEntity ? 'legal_entity' : 'individual'
            ]);
        }
        
        // Try to find existing subject
        $subject = Subject::where($searchField, $searchValue)->first();
        
        if (!$subject) {
            $orgFormId = $this->ensureOrgFormExists();
            
            $subjectData = [
                'is_legal_entity' => $isLegalEntity,
                'is_active' => true,
                'country_code' => 'UZ',
                'is_resident' => true,
                'company_name' => $companyName,
            ];

            if ($isLegalEntity) {
                $subjectData['inn'] = $searchValue;
                $subjectData['org_form_id'] = $orgFormId;
            } else {
                $subjectData['pinfl'] = $searchValue;
                $subjectData['document_type'] = 'Паспорт';
            }

            try {
                $subject = Subject::create($subjectData);
                Log::debug("Subject created", [
                    'id' => $subject->id,
                    'row_number' => $rowNumber,
                    'type' => $isLegalEntity ? 'legal' : 'individual'
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to create subject", [
                    'row_number' => $rowNumber,
                    'data' => $subjectData,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }

        return $subject;
    }

    private function cleanIdentifier($value, $maxLength)
    {
        if (empty($value)) return '';
        
        // Handle scientific notation
        if (stripos($value, 'E+') !== false || stripos($value, 'E-') !== false) {
            $value = number_format((float)$value, 0, '', '');
        }
        
        // Remove non-alphanumeric characters and limit length
        $cleaned = preg_replace('/[^\w]/', '', $value);
        return substr($cleaned, 0, $maxLength);
    }

    private function isLegalEntityByName($companyName)
    {
        $legalPatterns = ['OOO', 'МЧЖ', 'MCHJ', 'ООО', 'ТОО', 'LLC', 'АЖ', 'СП', 'ТИФ'];
        
        foreach ($legalPatterns as $pattern) {
            if (stripos($companyName, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }

    private function ensureOrgFormExists()
    {
        $orgForm = \App\Models\OrgForm::where('id', 1)->first();
        
        if (!$orgForm) {
            try {
                $orgForm = \App\Models\OrgForm::create([
                    'id' => 1,
                    'name_ru' => 'ООО',
                    'name_uz' => 'МЧЖ',
                    'is_active' => true
                ]);
                Log::info("Created default OrgForm", ['id' => $orgForm->id]);
            } catch (\Exception $e) {
                Log::error("Failed to create default OrgForm", ['error' => $e->getMessage()]);
                throw new \Exception("Cannot create required OrgForm");
            }
        }
        
        return $orgForm->id;
    }

    private function createContract($subject, $object, $contractNumber, $contractStatus, $contractDate, 
                                    $completionDate, $contractAmount, $paymentTerms, $paymentPeriod, 
                                    $advancePercent, $baseAmount, $statuses, $rowNumber = null)
    {
        // Parse payment terms
        $initialPaymentPercent = 20; // Default
        if (!empty($paymentTerms)) {
            if (preg_match('/(\d+)\/(\d+)/', $paymentTerms, $matches)) {
                $initialPaymentPercent = (int)$matches[1];
            } elseif (preg_match('/(\d+)/', $paymentTerms, $matches)) {
                $initialPaymentPercent = (int)$matches[1];
            }
        }
        
        if ($advancePercent > 0) {
            $initialPaymentPercent = $advancePercent;
        }

        // Get contract status
        $status = $this->getContractStatus($contractStatus, $statuses);
        $isActive = !$this->isContractCancelled($contractStatus);
        
        // Validate dates
        if (!$contractDate) {
            $contractDate = now();
            Log::warning("Missing contract date, using current date", ['row_number' => $rowNumber]);
        }

        // Calculate quarters
        $quartersCount = max(1, ceil($paymentPeriod / 3));
        
        // Ensure unique contract number
        if (empty($contractNumber)) {
            $contractNumber = 'APZ-' . now()->format('Y') . '-' . str_pad($rowNumber, 6, '0', STR_PAD_LEFT);
        }

        try {
            $contract = Contract::create([
                'contract_number' => $contractNumber,
                'object_id' => $object->id,
                'subject_id' => $subject->id,
                'contract_date' => $contractDate,
                'completion_date' => $completionDate,
                'status_id' => $status->id,
                'base_amount_id' => $baseAmount->id,
                'contract_volume' => $object->construction_volume,
                'coefficient' => $contractAmount > 0 && $object->construction_volume > 0 ? 
                    ($contractAmount / ($object->construction_volume * $baseAmount->amount)) : 1.0,
                'total_amount' => $contractAmount,
                'payment_type' => $initialPaymentPercent >= 100 ? 'full' : 'installment',
                'initial_payment_percent' => $initialPaymentPercent,
                'construction_period_years' => max(1, ceil($paymentPeriod / 12)),
                'quarters_count' => $quartersCount,
                'is_active' => $isActive,
            ]);

            return $contract;
        } catch (\Exception $e) {
            Log::error("Failed to create contract", [
                'row_number' => $rowNumber,
                'contract_number' => $contractNumber,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function getContractStatus($contractStatus, $statuses)
    {
        if (!empty($contractStatus)) {
            if (stripos($contractStatus, 'Бекор') !== false) {
                $cancelled = $statuses->where('name_ru', 'Бекор қилинган')->first();
                if ($cancelled) return $cancelled;
            } elseif (stripos($contractStatus, 'завершен') !== false || stripos($contractStatus, 'якун') !== false) {
                $completed = $statuses->where('name_ru', 'Якунланган')->first();
                if ($completed) return $completed;
            }
        }
        
        // Default to active status
        $active = $statuses->where('name_ru', 'Действующий')->first() ?: 
                 $statuses->where('name_ru', 'Амал қилувчи')->first() ?: 
                 $statuses->first();
                 
        if (!$active) {
            throw new \Exception("No contract statuses found in database");
        }
        
        return $active;
    }

    private function isContractCancelled($contractStatus)
    {
        return !empty($contractStatus) && (
            stripos($contractStatus, 'Бекор') !== false || 
            stripos($contractStatus, 'отменен') !== false ||
            stripos($contractStatus, 'cancelled') !== false
        );
    }

    private function logSkippedRow($rowNumber, $reason, $row)
    {
        if (!isset($this->skippedRows[$reason])) {
            $this->skippedRows[$reason] = [];
        }
        
        $this->skippedRows[$reason][] = [
            'row' => $rowNumber,
            'sample_data' => [
                'company_name' => $row[5] ?? '',
                'contract_number' => $row[6] ?? '',
                'inn' => $row[3] ?? '',
                'pinfl' => $row[4] ?? '',
                'district' => $row[13] ?? '',
                'amount' => $row[15] ?? ''
            ]
        ];
        
        Log::info("Row {$rowNumber} skipped: {$reason}", [
            'row_number' => $rowNumber,
            'reason' => $reason,
            'company_name' => $row[5] ?? '',
            'contract_number' => $row[6] ?? ''
        ]);
    }

    private function showImportSummary($processedCount, $skippedCount, $errorCount)
    {
        $this->command->info("\n" . str_repeat("=", 80));
        $this->command->info("APZ DATA IMPORT SUMMARY");
        $this->command->info(str_repeat("=", 80));
        $this->command->info("✅ Successfully processed: {$processedCount} records");
        $this->command->info("⏭️  Skipped rows: {$skippedCount}");
        $this->command->info("❌ Errors encountered: {$errorCount}");
        
        // Show unmapped districts with row numbers
        if (!empty($this->unmappedDistricts)) {
            $this->command->warn("\n⚠️  UNMAPPED DISTRICTS FOUND:");
            foreach ($this->unmappedDistricts as $districtName => $rowNumbers) {
                $rowList = implode(', ', $rowNumbers);
                $this->command->line("   📍 '{$districtName}' in rows: {$rowList}");
            }
            $this->command->info("💡 These districts were mapped to fallback districts. Check logs for details.");
        }
        
        // Show skipped rows with details
        if ($skippedCount > 0) {
            $this->command->warn("\n📋 SKIPPED ROWS DETAILS:");
            $totalSkippedShown = 0;
            foreach ($this->skippedRows as $reason => $rows) {
                $count = count($rows);
                $this->command->line("\n   ❌ {$reason}: {$count} rows");
                
                // Show first 10 rows for each reason
                $rowsToShow = array_slice($rows, 0, 10);
                foreach ($rowsToShow as $rowData) {
                    $rowNum = $rowData['row'];
                    $sample = $rowData['sample_data'];
                    $companyName = !empty($sample['company_name']) ? $sample['company_name'] : 'N/A';
                    $contractNum = !empty($sample['contract_number']) ? $sample['contract_number'] : 'N/A';
                    
                    $this->command->line("      Row {$rowNum}: {$companyName} | Contract: {$contractNum}");
                }
                
                if ($count > 10) {
                    $remaining = $count - 10;
                    $this->command->line("      ... and {$remaining} more rows");
                }
                $totalSkippedShown += min($count, 10);
            }
        }
        
        // Show database districts for reference
        $this->command->info("\n📍 AVAILABLE DISTRICTS IN DATABASE:");
        $availableDistricts = District::where('is_active', true)->pluck('name_ru')->toArray();
        $this->command->line("   " . implode(', ', $availableDistricts));
        
        $this->command->info("\n📄 Detailed logs: storage/logs/laravel.log");
        $this->command->info("🔧 To fix unmapped districts, either:");
        $this->command->info("   1. Add missing districts to database, OR");
        $this->command->info("   2. Update Excel file with correct district names");
        $this->command->info(str_repeat("=", 80));
        
        // Log summary for file records
        Log::info("APZ import completed", [
            'processed' => $processedCount,
            'skipped' => $skippedCount,
            'errors' => $errorCount,
            'unmapped_districts' => $this->unmappedDistricts,
            'skipped_details' => $this->skippedRows
        ]);
    }

    // Include all the remaining helper methods (parseActualPayment, createPaymentSchedule, etc.)
    // ... [Rest of the helper methods remain the same as in your original code]
    
    private function parseActualPayment($rawValue, $contractAmount, $totalPayment)
    {
        if (empty($rawValue) || $rawValue === '-' || $rawValue === 0) {
            return 0;
        }
        
        $numericValue = $this->parseAmount($rawValue);
        
        if ($numericValue > 0 && $numericValue <= 1) {
            $baseAmount = $totalPayment > 0 ? $totalPayment : $contractAmount;
            return $baseAmount * $numericValue;
        }
        
        if ($numericValue > 1) {
            return $numericValue;
        }
        
        return 0;
    }

    private function createPaymentSchedule($contract, $monthlyPayment = 0, $totalPayment = 0, $rowNumber = null)
    {
        if ($contract->payment_type === 'full') {
            return;
        }

        $remainingAmount = $contract->total_amount * (100 - $contract->initial_payment_percent) / 100;
        
        if ($totalPayment > 0 && $totalPayment < $contract->total_amount) {
            $remainingAmount = $totalPayment;
        }
        
        $quarterAmount = $monthlyPayment > 0 ? 
            ($monthlyPayment * 3) : 
            ($remainingAmount / max(1, $contract->quarters_count));

        $startYear = $contract->contract_date->year;
        $startQuarter = ceil($contract->contract_date->month / 3);

        try {
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
        } catch (\Exception $e) {
            Log::error("Failed to create payment schedule", [
                'row_number' => $rowNumber,
                'contract_id' => $contract->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function createActualPayments($contract, $paidAmount, $rowNumber = null)
    {
        if ($paidAmount <= 0) return;

        try {
            ActualPayment::create([
                'contract_id' => $contract->id,
                'payment_date' => $contract->contract_date,
                'amount' => $paidAmount,
                'year' => $contract->contract_date->year,
                'quarter' => ceil($contract->contract_date->month / 3),
                'payment_number' => 'APZ-IMPORT-' . $contract->id,
                'notes' => 'Импортировано из APZ файла',
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create actual payment", [
                'row_number' => $rowNumber,
                'contract_id' => $contract->id,
                'amount' => $paidAmount,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function createCouncilConclusion($object, $conclusion1, $conclusion2, $rowNumber = null)
    {
        $conclusionText = trim($conclusion1 . ' ' . $conclusion2);
        
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
            Log::error("Failed to create council conclusion", [
                'row_number' => $rowNumber,
                'object_id' => $object->id,
                'error' => $e->getMessage()
            ]);
            // Don't throw - this is optional data
        }
    }

    // Helper methods (readXlsx, parseExcelDate, etc.) remain the same...
    private function readXlsx($filePath)
    {
        $zip = new ZipArchive;
        
        if ($zip->open($filePath) !== TRUE) {
            throw new \Exception('Cannot open XLSX file');
        }

        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $this->parseSharedStrings($sharedStringsXml);
        }

        $worksheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($worksheetXml === false) {
            throw new \Exception('Cannot read worksheet data');
        }

        $zip->close();
        return $this->parseWorksheet($worksheetXml);
    }

    private function parseSharedStrings($xml)
    {
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
    }

    private function parseWorksheet($xml)
    {
        $worksheet = new SimpleXMLElement($xml);
        $data = [];
        
        if (!isset($worksheet->sheetData->row)) {
            return $data;
        }

        foreach ($worksheet->sheetData->row as $row) {
            $rowData = [];
            $maxCol = 0;
            
            $cells = [];
            if (isset($row->c)) {
                foreach ($row->c as $cell) {
                    $cellRef = (string)$cell['r'];
                    $colIndex = $this->columnIndexFromString($cellRef);
                    $maxCol = max($maxCol, $colIndex);
                    
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
                    
                    $cells[$colIndex] = $value;
                }
            }
            
            for ($i = 0; $i <= $maxCol; $i++) {
                $rowData[] = isset($cells[$i]) ? $cells[$i] : '';
            }
            
            $data[] = $rowData;
        }
        
        return $data;
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

    private function isHeaderRow($row)
    {
        $headerKeywords = ['ИНН', 'ПИНФЛ', 'Корхона', 'шарт', 'Контракт', 'санаси'];
        
        foreach ($row as $cell) {
            $cellText = strtolower(trim($cell));
            foreach ($headerKeywords as $keyword) {
                if (stripos($cellText, strtolower($keyword)) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    private function isEmptyRow($row)
    {
        if (empty($row)) return true;
        
        foreach ($row as $cell) {
            if (trim($cell) !== '') {
                return false;
            }
        }
        return true;
    }

    private function isSummaryRow($row)
    {
        foreach ($row as $cell) {
            if (stripos($cell, 'ЖАМИ') !== false) {
                return true;
            }
        }
        return false;
    }

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

    private function cleanString($value)
    {
        return trim(preg_replace('/\s+/', ' ', $value));
    }

    private function parseExcelDate($value)
    {
        if (empty($value) || $value === '-' || $value === '00.01.1900') return null;
        
        try {
            if (is_numeric($value) && $value > 1) {
                $excelEpoch = Carbon::create(1900, 1, 1);
                return $excelEpoch->addDays((int)$value - 2);
            }
            
            if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $value)) {
                return Carbon::createFromFormat('d.m.Y', $value);
            }
            
            $formats = ['d.m.Y', 'Y-m-d', 'd/m/Y', 'm/d/Y'];
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
        if (empty($value) || $value === '-' || stripos($value, '#REF') !== false) return 0;
        
        if (stripos($value, 'E+') !== false || stripos($value, 'E-') !== false) {
            return (float)$value;
        }
        
        $cleaned = str_replace(',', '.', $value);
        $cleaned = preg_replace('/[^\d.-]/', '', $cleaned);
        
        return (float)$cleaned;
    }

    private function parsePercent($value)
    {
        if (empty($value) || $value === '-') return 0;
        
        if (is_numeric($value) && $value <= 1) {
            return $value * 100;
        }
        
        $cleaned = str_replace(['%', ','], ['', '.'], $value);
        $cleaned = preg_replace('/[^\d.-]/', '', $cleaned);
        
        return (float)$cleaned;
    }
}
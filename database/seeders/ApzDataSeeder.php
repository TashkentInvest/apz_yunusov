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
    
    public function run()
    {
        $filePath = public_path('apz_data.xlsx');
        
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
            
            Log::info("Essential data check - Districts: {$districtCount}, Statuses: {$statusCount}, OrgForms: {$orgFormCount}");
            
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
            
            // Remove summary row (ЖАМИ row)
            if (count($data) > 0 && stripos($data[0][4] ?? '', 'ЖАМИ') !== false) {
                array_shift($data);
                Log::info('Summary row removed');
            }

            // Get required reference data
            $districts = $this->getDistricts();
            $statuses = $this->getContractStatuses();
            $baseAmount = $this->getOrCreateBaseAmount();
            
            Log::info("Reference data loaded - Districts: " . $districts->count() . ", Statuses: " . $statuses->count());

            DB::beginTransaction();
            
            $processedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;
            
            foreach ($data as $index => $row) {
                if ($this->isEmptyRow($row) || $this->isSummaryRow($row)) {
                    $skippedCount++;
                    continue;
                }

                try {
                    $this->processApzRow($row, $districts, $statuses, $baseAmount, $index + 3); // +3 because we removed 2 rows
                    $processedCount++;
                    
                    if ($processedCount % 10 == 0) {
                        $this->command->info("Processed {$processedCount} records...");
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $errorMessage = "Error processing row " . ($index + 3) . ": " . $e->getMessage();
                    $this->command->warn($errorMessage);
                    Log::error($errorMessage, [
                        'row_data' => $row,
                        'exception' => $e->getTraceAsString()
                    ]);
                    
                    // Show first few errors in detail
                    if ($errorCount <= 5) {
                        $this->command->error("Detailed error: " . $e->getFile() . ':' . $e->getLine());
                    }
                    continue; // Continue with next row
                }
            }

            DB::commit();
            $this->command->info("APZ data import completed!");
            $this->command->info("Successfully processed: {$processedCount} records");
            $this->command->info("Skipped empty rows: {$skippedCount}");
            $this->command->info("Errors encountered: {$errorCount}");
            
            Log::info("APZ data import completed - Processed: {$processedCount}, Skipped: {$skippedCount}, Errors: {$errorCount}");
            
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
        // Log the row being processed for debugging
        Log::debug("Processing row {$rowNumber}", ['row_data' => array_slice($row, 0, 10)]);
        
        // CORRECT COLUMN MAPPING based on your actual Excel structure:
        $councilConclusion = $this->cleanString($row[0] ?? ''); // Column A
        $rowId = $this->cleanString($row[3] ?? ''); // Column D - Row number
        $inn = $this->cleanString($row[4] ?? ''); // Column E - ИНН
        $pinfl = $this->cleanString($row[5] ?? ''); // Column F - ПИНФЛ  
        $companyName = $this->cleanString($row[6] ?? ''); // Column G - Корхона номи
        $contractNumber = $this->cleanString($row[7] ?? ''); // Column H - шарт. №
        $contractStatus = $this->cleanString($row[8] ?? ''); // Column I - Контракт ҳолати
        $contractDate = $this->parseExcelDate($row[9] ?? ''); // Column J - шартнома санаси
        $completionDate = $this->parseExcelDate($row[10] ?? ''); // Column K - Якунлаш сана
        $paymentTerms = $this->cleanString($row[11] ?? ''); // Column L - Тўлов шарти
        $paymentPeriod = (int)($row[12] ?? 0); // Column M - Тўлов муддати
        $advancePercent = $this->parsePercent($row[13] ?? ''); // Column N - Аванс
        $districtName = $this->cleanString($row[14] ?? ''); // Column O - Туман
        $area = $this->parseAmount($row[15] ?? 0); // Column P - М3 (площадь)
        $contractAmount = $this->parseAmount($row[16] ?? 0); // Column Q - Шартнома қиймати
        $scheduledPayment = $this->parseAmount($row[17] ?? 0); // Column R - Бўнак тўлов
        $monthlyPayment = $this->parseAmount($row[18] ?? 0); // Column S - Ойлик тўлов
        $totalPayment = $this->parseAmount($row[19] ?? 0); // Column T - Жами тўлов
        $remaining = $this->parseAmount($row[20] ?? 0); // Column U - Қолдиқ
        $actualPayment = $this->parseAmount($row[21] ?? 0); // Column V - ФАКТ
        
        // Log parsed values
        Log::debug("Row {$rowNumber} parsed values", [
            'inn' => $inn,
            'pinfl' => $pinfl,
            'company_name' => $companyName,
            'contract_number' => $contractNumber,
            'district_name' => $districtName,
            'contract_amount' => $contractAmount
        ]);
        
        // Skip if this is clearly not a data row
        if (empty($inn) && empty($pinfl) && empty($companyName) && empty($contractNumber)) {
            Log::debug("Row {$rowNumber} skipped - insufficient data");
            return;
        }
        
        // Skip rows with #REF! errors or summary data
        if (stripos($inn, '#REF') !== false || stripos($companyName, '#REF') !== false) {
            Log::debug("Row {$rowNumber} skipped - contains #REF error");
            return;
        }

        // Skip if no meaningful company name
        if (empty($companyName) || stripos($companyName, 'ЖАМИ') !== false) {
            Log::debug("Row {$rowNumber} skipped - no company name");
            return;
        }

        $this->command->info("Processing row {$rowNumber}: {$companyName} (Contract: {$contractNumber})");

        try {
            // 1. Create or find Subject
            $subject = $this->createOrFindSubject($inn, $pinfl, $companyName, $rowNumber);
            if (!$subject) {
                throw new \Exception("Failed to create or find subject");
            }
            Log::debug("Row {$rowNumber} - Subject created/found with ID: " . $subject->id);
            
            // 2. Create Object
            $object = $this->createObject($subject, $districtName, $districts, $contractAmount, $baseAmount, $area, $rowNumber);
            if (!$object) {
                throw new \Exception("Failed to create object");
            }
            Log::debug("Row {$rowNumber} - Object created with ID: " . $object->id);
            
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
            Log::debug("Row {$rowNumber} - Contract created with ID: " . $contract->id);

            // 4. Create Payment Schedule
            $this->createPaymentSchedule($contract, $monthlyPayment, $totalPayment, $rowNumber);

            // 5. Create Actual Payments (if any)
            if ($actualPayment > 0) {
                $this->createActualPayments($contract, $actualPayment, $rowNumber);
            }

            // 6. Create Council Conclusion (if exists)
            if ($councilConclusion) {
                $this->createCouncilConclusion($object, $councilConclusion, $rowNumber);
            }

        } catch (\Exception $e) {
            // Log the error but don't stop the entire process
            $errorMessage = "Failed to process row {$rowNumber}: " . $e->getMessage();
            $this->command->error($errorMessage);
            Log::error($errorMessage, [
                'row_data' => $row,
                'exception' => $e->getTraceAsString()
            ]);
            throw $e; // Re-throw to be caught by the caller
        }
    }

    private function createOrFindSubject($inn, $pinfl, $companyName, $rowNumber)
    {
        Log::debug("Row {$rowNumber} - Creating/finding subject", ['inn' => $inn, 'pinfl' => $pinfl, 'company' => $companyName]);
        
        // Clean the values
        $inn = trim($inn);
        $pinfl = trim($pinfl);
        $companyName = trim($companyName);
        
        // Clean INN - remove suffixes like "-1", "-2", "(1)", etc. and limit to 9 characters
        if (!empty($inn)) {
            $inn = preg_replace('/[-\(\)]\d+$/', '', $inn); // Remove -1, -2, (1), etc.
            $inn = preg_replace('/[^\d]/', '', $inn); // Keep only digits
            $inn = substr($inn, 0, 9); // Limit to 9 characters max
        }
        
        // Clean PINFL - limit to 14 characters
        if (!empty($pinfl)) {
            $pinfl = preg_replace('/[-\(\)]\d+$/', '', $pinfl); // Remove suffixes
            $pinfl = preg_replace('/[^\w]/', '', $pinfl); // Keep only alphanumeric
            $pinfl = substr($pinfl, 0, 14); // Limit to 14 characters max
        }
        
        // Determine if it's a legal entity or individual
        $isLegalEntity = !empty($inn) || 
                        stripos($companyName, 'OOO') !== false || 
                        stripos($companyName, 'МЧЖ') !== false || 
                        stripos($companyName, 'MCHJ') !== false ||
                        stripos($companyName, 'ООО') !== false ||
                        stripos($companyName, 'ТОО') !== false ||
                        stripos($companyName, 'LLC') !== false ||
                        stripos($companyName, 'АЖ') !== false ||
                        stripos($companyName, 'мчж') !== false;
        
        $searchField = $isLegalEntity ? 'inn' : 'pinfl';
        $searchValue = $isLegalEntity ? $inn : $pinfl;
        
        // Generate temporary ID if empty
        if (empty($searchValue)) {
            if ($isLegalEntity) {
                $searchValue = 'T' . substr(md5($companyName . microtime() . $rowNumber), 0, 8); // 9 chars total
                $searchField = 'inn';
            } else {
                $searchValue = 'T' . substr(md5($companyName . microtime() . $rowNumber), 0, 13); // 14 chars total
                $searchField = 'pinfl';
            }
        }
        
        // Skip if we still have empty values or company name is empty
        if (empty($searchValue) || empty($companyName)) {
            Log::error("Row {$rowNumber} - Cannot create subject: insufficient data", [
                'inn' => $inn, 'pinfl' => $pinfl, 'company_name' => $companyName
            ]);
            throw new \Exception("Cannot create subject: insufficient data (INN: '{$inn}', PINFL: '{$pinfl}', Name: '{$companyName}')");
        }

        // Try to find existing subject
        $subject = Subject::where($searchField, $searchValue)->first();
        
        if (!$subject) {
            // Ensure we have org_form_id for legal entities
            $orgFormId = 1; // Default
            $orgFormExists = \App\Models\OrgForm::where('id', $orgFormId)->exists();
            if (!$orgFormExists) {
                Log::warning("Row {$rowNumber} - OrgForm with ID {$orgFormId} not found, creating default");
                // Create default org form if it doesn't exist
                try {
                    \App\Models\OrgForm::create([
                        'id' => 1,
                        'name_ru' => 'ООО',
                        'name_uz' => 'МЧЖ',
                        'is_active' => true
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to create default OrgForm", ['exception' => $e->getMessage()]);
                }
            }
            
            $subjectData = [
                'is_legal_entity' => $isLegalEntity,
                'is_active' => true,
                'country_code' => 'UZ',
                'is_resident' => true,
            ];

            if ($isLegalEntity) {
                $subjectData += [
                    'company_name' => $companyName,
                    'inn' => $searchValue,
                    'org_form_id' => $orgFormId,
                ];
            } else {
                $subjectData += [
                    'pinfl' => $searchValue,
                    'document_type' => 'Паспорт',
                    'company_name' => $companyName,
                ];
            }

            try {
                $subject = Subject::create($subjectData);
                Log::debug("Row {$rowNumber} - Subject created", ['id' => $subject->id, 'data' => $subjectData]);
            } catch (\Exception $e) {
                Log::error("Row {$rowNumber} - Failed to create subject", [
                    'data' => $subjectData,
                    'exception' => $e->getMessage()
                ]);
                throw $e;
            }
        } else {
            Log::debug("Row {$rowNumber} - Subject found", ['id' => $subject->id]);
        }

        return $subject;
    }

    private function createObject($subject, $districtName, $districts, $contractAmount, $baseAmount, $area = null, $rowNumber = null)
    {
        Log::debug("Row {$rowNumber} - Creating object", ['district_name' => $districtName]);
        
        // Map district names to database values
        $districtMapping = [
            'Олмазор' => 'Олмазор',
            'Мирзо-Улуғбек' => 'Мирзо Улуғбек', 
            'Яккасарой' => 'Яккасарой',
            'Шайхонтохур' => 'Шайхонтохур',
            'Сергели' => 'Сергели',
            'Яшнобод' => 'Юнусобод',
            'Миробод' => 'Мирабад',
            'Янгихаёт' => 'Олмазор',
            'Учтепа' => 'Учтепа',
            'Чилонзор' => 'Чилонзор',
            'Бектемир' => 'Бектемир',
        ];
        
        $mappedDistrictName = $districtMapping[$districtName] ?? 'Олмазор';
        $district = $districts->where('name_ru', $mappedDistrictName)->first();
        
        if (!$district) {
            $district = $districts->first(); // Use first available district
        }
        
        if (!$district) {
            Log::error("Row {$rowNumber} - No districts found in database");
            throw new \Exception("No districts found in database. Please run district seeder first.");
        }

        Log::debug("Row {$rowNumber} - District selected", ['id' => $district->id, 'name' => $district->name_ru]);

        // Use provided area or calculate from contract amount
        $volume = $area > 0 ? $area : 
                 ($contractAmount > 0 ? ($contractAmount / $baseAmount->amount) : 1);

        try {
            $object = Objectt::create([
                'subject_id' => $subject->id,
                'district_id' => $district->id,
                'address' => 'APZ импорт - Манзил киритилмаган',
                'construction_volume' => $volume,
                'is_active' => true,
                'application_date' => now(),
            ]);
            
            Log::debug("Row {$rowNumber} - Object created", ['id' => $object->id, 'volume' => $volume]);
            return $object;
        } catch (\Exception $e) {
            Log::error("Row {$rowNumber} - Failed to create object", [
                'subject_id' => $subject->id,
                'district_id' => $district->id,
                'exception' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function createContract($subject, $object, $contractNumber, $contractStatus, $contractDate, 
                                    $completionDate, $contractAmount, $paymentTerms, $paymentPeriod, 
                                    $advancePercent, $baseAmount, $statuses, $rowNumber = null)
    {
        Log::debug("Row {$rowNumber} - Creating contract", [
            'contract_number' => $contractNumber,
            'status' => $contractStatus
        ]);
        
        // Parse initial payment percentage from payment terms
        $initialPaymentPercent = 20; // Default
        if (!empty($paymentTerms)) {
            if (preg_match('/(\d+)\/(\d+)/', $paymentTerms, $matches)) {
                $initialPaymentPercent = (int)$matches[1];
            } elseif (preg_match('/(\d+)/', $paymentTerms, $matches)) {
                $initialPaymentPercent = (int)$matches[1];
            }
        }
        
        // Use advance percent if available
        if ($advancePercent > 0) {
            $initialPaymentPercent = $advancePercent;
        }

        // Map contract status - ensure we have a valid status
        $defaultStatus = $statuses->where('name_ru', 'Амал қилувчи')->first();
        if (!$defaultStatus) {
            $defaultStatus = $statuses->first();
        }
        
        if (!$defaultStatus) {
            Log::error("Row {$rowNumber} - No contract statuses found");
            throw new \Exception("No contract statuses found in database");
        }
        
        $statusId = $defaultStatus->id;
        
        if (!empty($contractStatus)) {
            if (stripos($contractStatus, 'Бекор') !== false || stripos($contractStatus, 'отменен') !== false) {
                $cancelled = $statuses->where('name_ru', 'Бекор қилинган')->first();
                if ($cancelled) $statusId = $cancelled->id;
            } elseif (stripos($contractStatus, 'завершен') !== false || stripos($contractStatus, 'якун') !== false) {
                $completed = $statuses->where('name_ru', 'Якунланган')->first();
                if ($completed) $statusId = $completed->id;
            }
        }

        // Calculate quarters count
        $quartersCount = max(1, ceil($paymentPeriod / 3));
        
        // Generate contract number if empty
        if (empty($contractNumber)) {
            $contractNumber = 'APT-IMPORT-' . $subject->id . '-' . time() . '-' . $rowNumber;
        }

        try {
            $contract = Contract::create([
                'contract_number' => $contractNumber,
                'object_id' => $object->id,
                'subject_id' => $subject->id,
                'contract_date' => $contractDate ?: now(),
                'completion_date' => $completionDate,
                'status_id' => $statusId,
                'base_amount_id' => $baseAmount->id,
                'contract_volume' => $object->construction_volume,
                'coefficient' => $contractAmount > 0 && $object->construction_volume > 0 ? 
                    ($contractAmount / ($object->construction_volume * $baseAmount->amount)) : 1.0,
                'total_amount' => $contractAmount,
                'payment_type' => $initialPaymentPercent >= 100 ? 'full' : 'installment',
                'initial_payment_percent' => $initialPaymentPercent,
                'construction_period_years' => max(1, ceil($paymentPeriod / 12)),
                'quarters_count' => $quartersCount,
                'is_active' => stripos($contractStatus, 'Бекор') === false,
            ]);

            Log::debug("Row {$rowNumber} - Contract created", ['id' => $contract->id]);
            return $contract;
        } catch (\Exception $e) {
            Log::error("Row {$rowNumber} - Failed to create contract", [
                'contract_data' => [
                    'contract_number' => $contractNumber,
                    'object_id' => $object->id,
                    'subject_id' => $subject->id,
                    'status_id' => $statusId,
                    'base_amount_id' => $baseAmount->id,
                ],
                'exception' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function createPaymentSchedule($contract, $monthlyPayment = 0, $totalPayment = 0, $rowNumber = null)
    {
        if ($contract->payment_type === 'full') {
            Log::debug("Row {$rowNumber} - Skipping payment schedule for full payment contract");
            return;
        }

        Log::debug("Row {$rowNumber} - Creating payment schedule");

        $remainingAmount = $contract->total_amount * (100 - $contract->initial_payment_percent) / 100;
        
        // Use total payment if available, otherwise calculate
        if ($totalPayment > 0 && $totalPayment < $contract->total_amount) {
            $remainingAmount = $totalPayment;
        }
        
        // Use monthly payment if available, otherwise calculate
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
            Log::debug("Row {$rowNumber} - Payment schedule created with {$contract->quarters_count} quarters");
        } catch (\Exception $e) {
            Log::error("Row {$rowNumber} - Failed to create payment schedule", ['exception' => $e->getMessage()]);
            throw $e;
        }
    }

    private function createActualPayments($contract, $paidAmount, $rowNumber = null)
    {
        if ($paidAmount <= 0) return;

        Log::debug("Row {$rowNumber} - Creating actual payment", ['amount' => $paidAmount]);

        try {
            ActualPayment::create([
                'contract_id' => $contract->id,
                'payment_date' => $contract->contract_date,
                'amount' => $paidAmount,
                'year' => $contract->contract_date->year,
                'quarter' => ceil($contract->contract_date->month / 3),
                'payment_number' => 'APZ-IMPORT-' . $contract->id . '-' . $rowNumber,
                'notes' => 'APZ файлидан импорт қилинган',
            ]);
            Log::debug("Row {$rowNumber} - Actual payment created");
        } catch (\Exception $e) {
            Log::error("Row {$rowNumber} - Failed to create actual payment", ['exception' => $e->getMessage()]);
            throw $e;
        }
    }

    private function createCouncilConclusion($object, $conclusion, $rowNumber = null)
    {
        Log::debug("Row {$rowNumber} - Creating council conclusion", ['conclusion' => $conclusion]);
        
        $status = 'pending';
        if (stripos($conclusion, 'да') !== false || stripos($conclusion, 'yes') !== false) {
            $status = 'approved';
        } elseif (stripos($conclusion, 'нет') !== false || stripos($conclusion, 'no') !== false) {
            $status = 'rejected';
        }

        try {
            CouncilConclusion::create([
                'object_id' => $object->id,
                'application_date' => now(),
                'conclusion_date' => now(),
                'status' => $status,
            ]);
            Log::debug("Row {$rowNumber} - Council conclusion created", ['status' => $status]);
        } catch (\Exception $e) {
            Log::error("Row {$rowNumber} - Failed to create council conclusion", ['exception' => $e->getMessage()]);
            throw $e;
        }
    }

    // Helper methods remain the same...
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
        return stripos($row[4] ?? '', 'ЖАМИ') !== false;
    }

    private function getDistricts()
    {
        $districts = District::where('is_active', true)->get();
        
        if ($districts->isEmpty()) {
            Log::error("No districts found in database");
            throw new \Exception("No districts found in database. Please run EssentialDataSeeder first.");
        }
        
        Log::info("Loaded districts", ['count' => $districts->count(), 'names' => $districts->pluck('name_ru')->toArray()]);
        return $districts;
    }

    private function getContractStatuses()
    {
        $statuses = ContractStatus::where('is_active', true)->get();
        
        if ($statuses->isEmpty()) {
            Log::error("No contract statuses found in database");
            throw new \Exception("No contract statuses found in database. Please run EssentialDataSeeder first.");
        }
        
        Log::info("Loaded contract statuses", ['count' => $statuses->count(), 'names' => $statuses->pluck('name_ru')->toArray()]);
        return $statuses;
    }

    private function getOrCreateBaseAmount()
    {
        $baseAmount = BaseCalculationAmount::where('is_current', true)->first();
        
        if (!$baseAmount) {
            Log::info("Creating default base amount");
            $baseAmount = BaseCalculationAmount::create([
                'amount' => 412000.00,
                'effective_from' => '2024-01-01',
                'is_current' => true
            ]);
        }

        Log::info("Base amount loaded", ['id' => $baseAmount->id, 'amount' => $baseAmount->amount]);
        return $baseAmount;
    }

    private function cleanString($value)
    {
        return trim(preg_replace('/\s+/', ' ', $value));
    }

    private function parseExcelDate($value)
    {
        if (empty($value) || $value === '-') return null;
        
        try {
            // Handle Excel numeric dates (like 45485)
            if (is_numeric($value) && $value > 1) {
                // Excel epoch starts from 1900-01-01 (but has a leap year bug, so we subtract 2)
                $excelEpoch = Carbon::create(1900, 1, 1);
                return $excelEpoch->addDays((int)$value - 2);
            }
            
            // Try common date formats
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
            Log::warning("Failed to parse date: {$value}", ['exception' => $e->getMessage()]);
            return null;
        }
    }

    private function parseAmount($value)
    {
        if (empty($value) || $value === '-' || stripos($value, '#REF') !== false) return 0;
        
        // Handle scientific notation (like 2.57165E+12)
        if (stripos($value, 'E') !== false) {
            return (float)$value;
        }
        
        $cleaned = preg_replace('/[^\d.-]/', '', str_replace(',', '.', $value));
        return (float)$cleaned;
    }

    private function parsePercent($value)
    {
        if (empty($value) || $value === '-') return 0;
        
        // Handle decimal percentages like 0.1999
        if (is_numeric($value) && $value < 1) {
            return $value * 100; // Convert 0.1999 to 19.99%
        }
        
        $cleaned = preg_replace('/[^\d.-]/', '', str_replace('%', '', $value));
        return (float)$cleaned;
    }
}
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
                    $this->processApzRow($row, $districts, $statuses, $baseAmount, $index + 2);
                    $processedCount++;
                    
                    if ($processedCount % 10 == 0) {
                        $this->command->info("Processed {$processedCount} records...");
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $errorMessage = "Error processing row " . ($index + 2) . ": " . $e->getMessage();
                    $this->command->warn($errorMessage);
                    Log::error($errorMessage, [
                        'row_data' => array_slice($row, 0, 15),
                        'exception' => $e->getTraceAsString()
                    ]);
                    
                    if ($errorCount <= 5) {
                        $this->command->error("Detailed error: " . $e->getFile() . ':' . $e->getLine());
                    }
                    continue;
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
        Log::debug("Processing row {$rowNumber}", ['row_data' => array_slice($row, 0, 15)]);
        
        // CORRECTED COLUMN MAPPING based on your data structure
        $councilConclusion1 = $this->cleanString($row[0] ?? ''); // Column A
        $councilConclusion2 = $this->cleanString($row[1] ?? ''); // Column B
        $rowId = $this->cleanString($row[2] ?? ''); // Column C
        $inn = $this->cleanString($row[3] ?? ''); // Column D - ИНН
        $pinfl = $this->cleanString($row[4] ?? ''); // Column E - ПИНФЛ  
        $companyName = $this->cleanString($row[5] ?? ''); // Column F - Корхона номи
        $contractNumber = $this->cleanString($row[6] ?? ''); // Column G - шарт. №
        $contractStatus = $this->cleanString($row[7] ?? ''); // Column H - Контракт ҳолати
        $contractDate = $this->parseExcelDate($row[8] ?? ''); // Column I - шартнома санаси
        $completionDate = $this->parseExcelDate($row[9] ?? ''); // Column J - Якунлаш сана
        $paymentTerms = $this->cleanString($row[10] ?? ''); // Column K - Тўлов шарти
        $paymentPeriod = (int)($row[11] ?? 0); // Column L - Тўлов муддати
        $advancePercent = $this->parsePercent($row[12] ?? ''); // Column M - Аванс
        $districtName = $this->cleanString($row[13] ?? ''); // Column N - Туман
        $area = $this->parseAmount($row[14] ?? 0); // Column O - М3
        $contractAmount = $this->parseAmount($row[15] ?? 0); // Column P - Шартнома қиймати
        $scheduledPayment = $this->parseAmount($row[16] ?? 0); // Column Q - Бўнак тўлов
        $monthlyPayment = $this->parseAmount($row[17] ?? 0); // Column R - Ойлик тўлов
        $totalPayment = $this->parseAmount($row[18] ?? 0); // Column S - Жами тўлов
        $remaining = $this->parseAmount($row[19] ?? 0); // Column T - Қолдиқ
        $actualPaymentRaw = $row[20] ?? 0; // Column U - ФАКТ (raw value)
        
        // CRITICAL FIX: Parse actual payment correctly
        $actualPayment = $this->parseActualPayment($actualPaymentRaw, $contractAmount, $totalPayment);
        
        Log::debug("Row {$rowNumber} parsed values", [
            'inn' => $inn,
            'pinfl' => $pinfl,
            'company_name' => $companyName,
            'contract_number' => $contractNumber,
            'district_name' => $districtName,
            'contract_amount' => $contractAmount,
            'actual_payment_raw' => $actualPaymentRaw,
            'actual_payment_calculated' => $actualPayment
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
            
            // 2. Create Object
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

        } catch (\Exception $e) {
            $errorMessage = "Failed to process row {$rowNumber}: " . $e->getMessage();
            Log::error($errorMessage, [
                'row_data' => array_slice($row, 0, 15),
                'exception' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * CRITICAL FIX: Parse actual payment correctly
     * The ФАКТ column can contain:
     * - Percentage (like 0.80 meaning 80% paid)
     * - Actual amount (large numbers)
     * - Zero or empty values
     */
    private function parseActualPayment($rawValue, $contractAmount, $totalPayment)
    {
        if (empty($rawValue) || $rawValue === '-' || $rawValue === 0) {
            return 0;
        }
        
        // Convert to float first
        $numericValue = $this->parseAmount($rawValue);
        
        // If it's a small decimal (like 0.80), treat as percentage
        if ($numericValue > 0 && $numericValue <= 1) {
            // Calculate actual payment as percentage of total payment or contract amount
            $baseAmount = $totalPayment > 0 ? $totalPayment : $contractAmount;
            $calculatedPayment = $baseAmount * $numericValue;
            
            Log::debug("Parsed payment as percentage", [
                'raw_value' => $rawValue,
                'percentage' => $numericValue,
                'base_amount' => $baseAmount,
                'calculated_payment' => $calculatedPayment
            ]);
            
            return $calculatedPayment;
        }
        
        // If it's a large number, treat as actual payment amount
        if ($numericValue > 1) {
            Log::debug("Parsed payment as amount", [
                'raw_value' => $rawValue,
                'parsed_amount' => $numericValue
            ]);
            
            return $numericValue;
        }
        
        return 0;
    }

    private function createOrFindSubject($inn, $pinfl, $companyName, $rowNumber)
    {
        Log::debug("Row {$rowNumber} - Creating/finding subject", ['inn' => $inn, 'pinfl' => $pinfl, 'company' => $companyName]);
        
        // Clean the values
        $inn = trim($inn);
        $pinfl = trim($pinfl);
        $companyName = trim($companyName);
        
        // Handle scientific notation in PINFL
        if (stripos($pinfl, 'E+') !== false || stripos($pinfl, 'E-') !== false) {
            $pinfl = number_format((float)$pinfl, 0, '', '');
        }
        
        // Clean INN - remove suffixes and limit to 9 characters
        if (!empty($inn)) {
            $inn = preg_replace('/[-\(\)]\d+$/', '', $inn);
            $inn = preg_replace('/[^\d]/', '', $inn);
            $inn = substr($inn, 0, 9);
        }
        
        // Clean PINFL - limit to 14 characters
        if (!empty($pinfl)) {
            $pinfl = preg_replace('/[-\(\)]\d+$/', '', $pinfl);
            $pinfl = preg_replace('/[^\d]/', '', $pinfl);
            $pinfl = substr($pinfl, 0, 14);
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
                        stripos($companyName, 'мчж') !== false ||
                        stripos($companyName, 'СП') !== false ||
                        stripos($companyName, 'ТИФ') !== false;
        
        $searchField = $isLegalEntity ? 'inn' : 'pinfl';
        $searchValue = $isLegalEntity ? $inn : $pinfl;
        
        // Generate temporary ID if empty
        if (empty($searchValue)) {
            if ($isLegalEntity) {
                $searchValue = 'T' . substr(md5($companyName . microtime() . $rowNumber), 0, 8);
                $searchField = 'inn';
            } else {
                $searchValue = 'T' . substr(md5($companyName . microtime() . $rowNumber), 0, 13);
                $searchField = 'pinfl';
            }
        }
        
        if (empty($searchValue) || empty($companyName)) {
            throw new \Exception("Cannot create subject: insufficient data (INN: '{$inn}', PINFL: '{$pinfl}', Name: '{$companyName}')");
        }

        // Try to find existing subject
        $subject = Subject::where($searchField, $searchValue)->first();
        
        if (!$subject) {
            // Ensure we have org_form_id for legal entities
            $orgFormId = 1;
            $orgFormExists = \App\Models\OrgForm::where('id', $orgFormId)->exists();
            if (!$orgFormExists) {
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
                Log::debug("Row {$rowNumber} - Subject created", ['id' => $subject->id]);
            } catch (\Exception $e) {
                Log::error("Row {$rowNumber} - Failed to create subject", [
                    'data' => $subjectData,
                    'exception' => $e->getMessage()
                ]);
                throw $e;
            }
        }

        return $subject;
    }

    private function createObject($subject, $districtName, $districts, $contractAmount, $baseAmount, $area = null, $rowNumber = null)
    {
        Log::debug("Row {$rowNumber} - Creating object", ['district_name' => $districtName]);
        
        // Map district names to database values
        $districtMapping = [
            'Олмазор' => 'Алмазарский',
            'Мирзо-Улуғбек' => 'Мирзо Улугбекский', 
            'Яккасарой' => 'Яккасарайский',
            'Шайхонтохур' => 'Шайхантахурский',
            'Сергели' => 'Сергелийский',
            'Яшнобод' => 'Юнусабадский',
            'Юнусобод' => 'Юнусабадский',
            'Миробод' => 'Мирабадский',
            'Янгихаёт' => 'Алмазарский',
            'Учтепа' => 'Учтепинский',
            'Чилонзор' => 'Чиланзарский',
            'Бектемир' => 'Бектемирский',
        ];
        
        $mappedDistrictName = $districtMapping[$districtName] ?? 'Алмазарский';
        $district = $districts->where('name_ru', $mappedDistrictName)->first();
        
        if (!$district) {
            // Try to match by similar name
            $district = $districts->filter(function($d) use ($districtName) {
                return stripos($d->name_ru, $districtName) !== false || 
                       stripos($districtName, $d->name_ru) !== false;
            })->first();
        }
        
        if (!$district) {
            $district = $districts->first();
        }
        
        if (!$district) {
            throw new \Exception("No districts found in database. Please run district seeder first.");
        }

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

        // Map contract status
        $defaultStatus = $statuses->where('name_ru', 'Действующий')->first() ?: 
                        $statuses->where('name_ru', 'Амал қилувчи')->first() ?: 
                        $statuses->first();
        
        if (!$defaultStatus) {
            throw new \Exception("No contract statuses found in database");
        }
        
        $statusId = $defaultStatus->id;
        
        if (!empty($contractStatus)) {
            if (stripos($contractStatus, 'Бекор') !== false || stripos($contractStatus, 'отменен') !== false) {
                $cancelled = $statuses->where('name_ru', 'Бекор қилинган')->first() ?: 
                           $statuses->where('name_ru', 'Отменен')->first();
                if ($cancelled) $statusId = $cancelled->id;
            } elseif (stripos($contractStatus, 'завершен') !== false || stripos($contractStatus, 'якун') !== false) {
                $completed = $statuses->where('name_ru', 'Якунланган')->first() ?: 
                           $statuses->where('name_ru', 'Завершен')->first();
                if ($completed) $statusId = $completed->id;
            }
        }

        // Calculate quarters count
        $quartersCount = max(1, ceil($paymentPeriod / 3));
        
        // Generate contract number if empty
        if (empty($contractNumber)) {
            $contractNumber = 'APT-IMPORT-' . $subject->id . '-' . time() . '-' . $rowNumber;
        }

        // Handle date parsing issues
        if (!$contractDate) {
            $contractDate = now();
        }

        try {
            $contract = Contract::create([
                'contract_number' => $contractNumber,
                'object_id' => $object->id,
                'subject_id' => $subject->id,
                'contract_date' => $contractDate,
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

            return $contract;
        } catch (\Exception $e) {
            Log::error("Row {$rowNumber} - Failed to create contract", [
                'exception' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function createPaymentSchedule($contract, $monthlyPayment = 0, $totalPayment = 0, $rowNumber = null)
    {
        if ($contract->payment_type === 'full') {
            return;
        }

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
        } catch (\Exception $e) {
            Log::error("Row {$rowNumber} - Failed to create payment schedule", ['exception' => $e->getMessage()]);
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
                'amount' => $paidAmount, // This should now be the correct amount
                'year' => $contract->contract_date->year,
                'quarter' => ceil($contract->contract_date->month / 3),
                'payment_number' => 'APZ-IMPORT-' . $contract->id . '-' . $rowNumber,
                'notes' => 'APZ файлидан импорт қилинган',
            ]);
            
            Log::debug("Row {$rowNumber} - Actual payment created", ['amount' => $paidAmount]);
        } catch (\Exception $e) {
            Log::error("Row {$rowNumber} - Failed to create actual payment", ['exception' => $e->getMessage()]);
            throw $e;
        }
    }

    private function createCouncilConclusion($object, $conclusion1, $conclusion2, $rowNumber = null)
    {
        $status = 'pending';
        
        $conclusionText = trim($conclusion1 . ' ' . $conclusion2);
        
        if (stripos($conclusionText, 'да') !== false || stripos($conclusionText, 'yes') !== false) {
            $status = 'approved';
        } elseif (stripos($conclusionText, 'нет') !== false || stripos($conclusionText, 'no') !== false) {
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
            
            // Fill in gaps with empty strings up to the maximum column
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
        // Check if any cell in the row contains summary keywords
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
        if (empty($value) || $value === '-' || $value === '00.01.1900') return null;
        
        try {
            // Handle Excel numeric dates (like 45485 = July 12, 2024)
            if (is_numeric($value) && $value > 1) {
                // Excel epoch starts from 1900-01-01 (but has a leap year bug, so we subtract 2)
                $excelEpoch = Carbon::create(1900, 1, 1);
                return $excelEpoch->addDays((int)$value - 2);
            }
            
            // Handle dd.mm.yyyy format
            if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $value, $matches)) {
                return Carbon::createFromFormat('d.m.Y', $value);
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
        if (stripos($value, 'E+') !== false || stripos($value, 'E-') !== false) {
            return (float)$value;
        }
        
        // Replace comma with dot for decimal separator
        $cleaned = str_replace(',', '.', $value);
        
        // Remove all non-numeric characters except dots and minus signs
        $cleaned = preg_replace('/[^\d.-]/', '', $cleaned);
        
        return (float)$cleaned;
    }

    private function parsePercent($value)
    {
        if (empty($value) || $value === '-') return 0;
        
        // Handle decimal percentages like 0.1999 (which is 19.99%)
        if (is_numeric($value) && $value <= 1) {
            return $value * 100; // Convert 0.1999 to 19.99%
        }
        
        // Remove % sign and convert to float
        $cleaned = str_replace('%', '', $value);
        $cleaned = str_replace(',', '.', $cleaned);
        $cleaned = preg_replace('/[^\d.-]/', '', $cleaned);
        
        return (float)$cleaned;
    }
}
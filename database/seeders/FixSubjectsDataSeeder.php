<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Contract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use ZipArchive;
use SimpleXMLElement;

class FixSubjectsDataSeeder extends Seeder
{
    private $sharedStrings = [];
    private $detailedLog = [];
    private $processedSubjects = [];
    private $stats = [
        'total_processed' => 0,
        'successfully_updated' => 0,
        'subjects_not_found' => 0,
        'invalid_inn_fixed' => 0,
        'entity_type_changed' => 0,
        'passport_serials_fixed' => 0,
        'pinfl_added' => 0,
        'name_fixed' => 0,
        'contract_numbers_updated' => 0,
        'dates_updated' => 0,
        'skipped_empty' => 0,
        'skipped_duplicates' => 0,
        'errors' => 0,
    ];

    private $columnMap = [
        'inn' => 0,              // Column A: INN or Passport
        'pinfl' => 1,            // Column B: PINFL
        'company_name' => 2,     // Column C: Company/Person Name
        'contract_number' => 3,  // Column D: Contract Number
        'contract_date' => 4,    // Column E: Contract Date
        'completion_date' => 5,  // Column F: Completion Date
    ];

    public function run()
    {
        $filePath = public_path('yunusov_subyektlar.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error('Fix data file not found: ' . $filePath);
            return;
        }

        $this->command->info('Starting PRODUCTION subjects fix by INN/PINFL matching...');
        $this->command->info(str_repeat("=", 80));

        try {
            $data = $this->readXlsx($filePath);

            if (empty($data)) {
                $this->command->error('No data found in XLSX file');
                return;
            }

            $headerRowIndex = $this->findHeaderRow($data);
            if ($headerRowIndex === -1) {
                throw new \Exception('Could not find header row');
            }

            $dataRows = array_slice($data, $headerRowIndex + 1);
            $this->command->info("Found " . count($dataRows) . " rows to process\n");

            $this->processFixRows($dataRows);

        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('Fix failed: ' . $e->getMessage());
            Log::error('Fix failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    private function processFixRows($dataRows)
    {
        DB::beginTransaction();

        try {
            foreach ($dataRows as $index => $row) {
                $rowNumber = $index + 1;
                $this->stats['total_processed']++;

                if ($this->isEmptyRow($row)) {
                    $this->stats['skipped_empty']++;
                    continue;
                }

                try {
                    $rowData = $this->extractRowData($row);

                    // Skip rows with no meaningful data
                    if (empty($rowData['companyName']) && empty($rowData['inn']) && empty($rowData['pinfl'])) {
                        $this->stats['skipped_empty']++;
                        continue;
                    }

                    // Find subject by INN/PINFL/Passport
                    $subject = $this->findSubjectByIdentifier($rowData);

                    if (!$subject) {
                        $this->stats['subjects_not_found']++;
                        $this->logDetail($rowNumber, 'SUBJECT_NOT_FOUND', [
                            'inn' => $rowData['inn'],
                            'pinfl' => $rowData['pinfl'],
                            'company' => $rowData['companyName']
                        ]);
                        $this->command->warn("Row {$rowNumber}: Subject not found - {$rowData['companyName']}");
                        continue;
                    }

                    // Check if we already processed this subject
                    if (isset($this->processedSubjects[$subject->id])) {
                        $this->stats['skipped_duplicates']++;
                        $this->command->info("Row {$rowNumber}: Skipping duplicate subject (already processed in row {$this->processedSubjects[$subject->id]})");
                        $this->logDetail($rowNumber, 'SKIPPED_DUPLICATE', [
                            'subject_id' => $subject->id,
                            'company' => $rowData['companyName'],
                            'first_processed_row' => $this->processedSubjects[$subject->id]
                        ]);
                        continue;
                    }

                    $this->fixSubjectWithLogging($subject, $rowData, $rowNumber);

                    // Mark subject as processed
                    $this->processedSubjects[$subject->id] = $rowNumber;

                } catch (\Exception $e) {
                    $this->stats['errors']++;
                    $this->command->error("Row {$rowNumber} error: " . $e->getMessage());
                    $this->logDetail($rowNumber, 'ERROR', ['error' => $e->getMessage()]);
                }
            }

            DB::commit();
            $this->showDetailedSummary();

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    private function findSubjectByIdentifier($rowData)
    {
        $cleanInn = $this->cleanINN($rowData['inn']);
        $cleanPinfl = $this->cleanPINFL($rowData['pinfl']);

        // 1. Try to find by valid 9-digit INN (legal entities)
        if ($this->isValidINN($cleanInn)) {
            $subject = Subject::where('inn', $cleanInn)->first();
            if ($subject) return $subject;
        }

        // 2. Try to find by passport serial (individuals)
        if ($this->isPassportSerial($cleanInn)) {
            $subject = Subject::where('document_series', $cleanInn)->first();
            if ($subject) return $subject;
        }

        // 3. Try to find by PINFL (individuals)
        if ($this->isValidPINFL($cleanPinfl)) {
            $subject = Subject::where('pinfl', $cleanPinfl)->first();
            if ($subject) return $subject;
        }

        // 4. Check if INN column contains PINFL (14 digits)
        if ($this->isValidPINFL($cleanInn)) {
            $subject = Subject::where('pinfl', $cleanInn)->first();
            if ($subject) return $subject;
        }

        // 5. Try partial company name match
        if (!empty($rowData['companyName']) && strlen($rowData['companyName']) > 5) {
            $subject = Subject::where('company_name', 'LIKE', '%' . substr($rowData['companyName'], 0, 20) . '%')
                ->first();
            if ($subject) return $subject;
        }

        return null;
    }

    private function fixSubjectWithLogging($subject, $rowData, $rowNumber)
    {
        // Store original values
        $original = [
            'inn' => $subject->inn,
            'pinfl' => $subject->pinfl,
            'company_name' => $subject->company_name,
            'is_legal_entity' => $subject->is_legal_entity,
            'document_series' => $subject->document_series,
        ];

        // Clean identifiers
        $rawInn = $rowData['inn'];
        $rawPinfl = $rowData['pinfl'];
        $cleanInn = $this->cleanINN($rawInn);
        $cleanPinfl = $this->cleanPINFL($rawPinfl);

        // Track if INN was cleaned
        if ($rawInn !== $cleanInn && !empty($rawInn)) {
            $this->stats['invalid_inn_fixed']++;
            $this->command->info("Row {$rowNumber}: Fixed INN '{$rawInn}' → '{$cleanInn}'");
        }

        // Determine entity type
        $isLegalEntity = $this->determineEntityType($cleanInn, $cleanPinfl, $rowData['companyName']);

        // Build updates - CRITICAL: For individuals, ensure company_name contains the person's name, NOT passport
        $updates = [
            'company_name' => $rowData['companyName'], // This must be the actual name from Excel
            'is_legal_entity' => $isLegalEntity,
        ];

        if ($isLegalEntity) {
            // Legal entity
            if (!empty($cleanInn) && $this->isValidINN($cleanInn)) {
                $updates['inn'] = $cleanInn;
                $updates['pinfl'] = null;
                $updates['document_series'] = null;
                $updates['document_type'] = null;
            } else {
                $this->command->warn("Row {$rowNumber}: No valid INN for legal entity: '{$rawInn}'");
                return;
            }
        } else {
            // Individual - IMPORTANT: Passport goes to document_series, name stays in company_name
            if ($this->isPassportSerial($cleanInn)) {
                $this->stats['passport_serials_fixed']++;
                $updates['document_series'] = $cleanInn;
                $updates['document_type'] = 'Паспорт';
                $updates['inn'] = null;
                $updates['pinfl'] = !empty($cleanPinfl) ? $cleanPinfl : null;
                // company_name already set above with person's actual name

                $this->command->info("Row {$rowNumber}: Fixed passport serial: {$cleanInn}");
            } elseif ($this->isValidPINFL($cleanPinfl)) {
                if (empty($original['pinfl'])) {
                    $this->stats['pinfl_added']++;
                }
                $updates['pinfl'] = $cleanPinfl;
                $updates['inn'] = !empty($cleanInn) && $this->isValidINN($cleanInn) ? $cleanInn : null;
                $updates['document_series'] = null;
                $updates['document_type'] = null;
                // company_name already set above with person's actual name
            } elseif ($this->isValidPINFL($cleanInn)) {
                if (empty($original['pinfl'])) {
                    $this->stats['pinfl_added']++;
                }
                $updates['pinfl'] = $cleanInn;
                $updates['inn'] = null;
                $updates['document_series'] = null;
                $updates['document_type'] = null;
                // company_name already set above with person's actual name
            }
        }

        // Track entity type changes
        if ($original['is_legal_entity'] !== $isLegalEntity) {
            $this->stats['entity_type_changed']++;
            $entityType = $isLegalEntity ? 'LEGAL' : 'INDIVIDUAL';
            $this->command->info("Row {$rowNumber}: Changed entity type to {$entityType}");
        }

        // Track name fixes
        if ($original['company_name'] !== $updates['company_name']) {
            $this->stats['name_fixed']++;
            $this->command->info("Row {$rowNumber}: Fixed name '{$original['company_name']}' → '{$updates['company_name']}'");
        }

        // Update subject
        $subject->update($updates);

        // Update contracts
        $contracts = Contract::where('subject_id', $subject->id)->get();

        if ($contracts->count() > 0 && !empty($rowData['contractNumber'])) {
            foreach ($contracts as $index => $contract) {
                $originalContractNumber = $contract->contract_number;

                // For multiple contracts, add suffix
                $newContractNumber = $rowData['contractNumber'];
                if ($index > 0) {
                    $newContractNumber .= '-' . ($index + 1);
                }

                try {
                    $contractUpdates = ['contract_number' => $newContractNumber];

                    if (!empty($rowData['contractDate'])) {
                        $contractUpdates['contract_date'] = $rowData['contractDate'];
                    }
                    if (!empty($rowData['completionDate'])) {
                        $contractUpdates['completion_date'] = $rowData['completionDate'];
                    }

                    $contract->update($contractUpdates);
                    $this->stats['contract_numbers_updated']++;

                    if ($originalContractNumber !== $newContractNumber) {
                        $this->command->info("Row {$rowNumber}: Updated contract '{$originalContractNumber}' → '{$newContractNumber}'");
                    }

                } catch (\Exception $e) {
                    Log::warning("Row {$rowNumber}: Skipped duplicate contract", [
                        'contract_id' => $contract->id,
                        'attempted_number' => $newContractNumber,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            if (isset($contractUpdates) && (isset($contractUpdates['contract_date']) || isset($contractUpdates['completion_date']))) {
                $this->stats['dates_updated']++;
            }
        }

        $this->stats['successfully_updated']++;

        // Detailed logging
        $changes = $this->detectChanges($original, $updates);
        if (!empty($changes)) {
            $this->logDetail($rowNumber, 'SUCCESS', [
                'company' => $rowData['companyName'],
                'changes' => $changes,
                'new_contract_number' => $rowData['contractNumber'],
            ]);
        }

        // Progress indicator
        if ($rowNumber % 20 == 0) {
            $this->command->info("\n✓ Progress: {$rowNumber} rows, {$this->stats['successfully_updated']} updated");
        }
    }

    private function detectChanges($original, $updates)
    {
        $changes = [];

        foreach ($updates as $key => $newValue) {
            $oldValue = $original[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'from' => $oldValue ?: 'NULL',
                    'to' => $newValue ?: 'NULL'
                ];
            }
        }

        return $changes;
    }

    private function logDetail($rowNumber, $status, $data)
    {
        $this->detailedLog[] = [
            'row' => $rowNumber,
            'status' => $status,
            'data' => $data,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ];

        Log::info("Row {$rowNumber} - {$status}", $data);
    }

    private function cleanINN($value)
    {
        if (empty($value)) return '';

        $value = trim(strtoupper((string)$value));

        // Remove suffixes like -1, -2, (1), (2)
        $cleaned = preg_replace('/[-\(]\d+[\)]?$/', '', $value);

        // Remove all non-alphanumeric
        $cleaned = preg_replace('/[^A-Z0-9]/', '', $cleaned);

        return $cleaned;
    }

    private function cleanPINFL($value)
    {
        if (empty($value)) return '';

        $cleaned = preg_replace('/[^\d]/', '', trim($value));

        return strlen($cleaned) === 14 ? $cleaned : '';
    }

    private function isValidINN($value)
    {
        return !empty($value) && preg_match('/^\d{9}$/', $value);
    }

    private function isValidPINFL($value)
    {
        return !empty($value) && preg_match('/^\d{14}$/', $value);
    }

    private function isPassportSerial($value)
    {
        return !empty($value) && preg_match('/^[A-Z]{2}\d{7}$/', strtoupper($value));
    }

    private function determineEntityType($cleanInn, $cleanPinfl, $companyName)
    {
        // Valid 9-digit INN = legal entity
        if ($this->isValidINN($cleanInn)) {
            return true;
        }

        // Passport or PINFL = individual
        if ($this->isPassportSerial($cleanInn) || $this->isValidPINFL($cleanPinfl)) {
            return false;
        }

        // Check name patterns
        $legalPatterns = [
            'OOO', 'ООО', 'MCHJ', 'МЧЖ', 'MChJ', 'СП', 'SP',
            'AJ', 'АЖ', 'JSC', 'LLC', 'TOO', 'ТОО',
            'XK', 'ХК', 'TIF', 'ТИФ', 'МЧ', 'ИП'
        ];

        $upperName = strtoupper($companyName);
        foreach ($legalPatterns as $pattern) {
            if (strpos($upperName, $pattern) !== false) {
                return true;
            }
        }

        $individualPatterns = ["o'g'li", "o'gli", "qizi", 'ovich', 'evich', 'ovna', 'evna'];
        foreach ($individualPatterns as $pattern) {
            if (stripos($companyName, $pattern) !== false) {
                return false;
            }
        }

        // Default to legal entity if unclear
        return true;
    }

    private function showDetailedSummary()
    {
        $this->command->info("\n" . str_repeat("=", 80));
        $this->command->info("PRODUCTION FIX SUMMARY - COMPLETE");
        $this->command->info(str_repeat("=", 80));

        $this->command->info("\n📊 STATISTICS:");
        $this->command->info("   Total rows processed: {$this->stats['total_processed']}");
        $this->command->info("   ✅ Successfully updated: {$this->stats['successfully_updated']}");
        $this->command->info("   ⚠️  Subjects not found: {$this->stats['subjects_not_found']}");
        $this->command->info("   🔄 Skipped (duplicates): {$this->stats['skipped_duplicates']}");
        $this->command->info("   ⏭️  Skipped (empty): {$this->stats['skipped_empty']}");
        $this->command->info("   ❌ Errors: {$this->stats['errors']}");

        $this->command->info("\n🔧 CHANGES MADE:");
        $this->command->info("   - Invalid INNs cleaned: {$this->stats['invalid_inn_fixed']}");
        $this->command->info("   - Names corrected: {$this->stats['name_fixed']}");
        $this->command->info("   - Entity type changed: {$this->stats['entity_type_changed']}");
        $this->command->info("   - Passport serials fixed: {$this->stats['passport_serials_fixed']}");
        $this->command->info("   - PINFL values added: {$this->stats['pinfl_added']}");
        $this->command->info("   - Contract numbers updated: {$this->stats['contract_numbers_updated']}");
        $this->command->info("   - Contract dates updated: {$this->stats['dates_updated']}");

        $this->saveDetailedLogToFile();

        if ($this->stats['successfully_updated'] > 0) {
            $this->command->info("\n✅ Import completed successfully!");
        }

        if ($this->stats['subjects_not_found'] > 0) {
            $this->command->warn("\n⚠️  {$this->stats['subjects_not_found']} subjects were not found in the database");
        }

        if ($this->stats['skipped_duplicates'] > 0) {
            $this->command->info("\n🔄 {$this->stats['skipped_duplicates']} duplicate entries were skipped (subject already processed)");
        }

        $this->command->info(str_repeat("=", 80));
    }

    private function saveDetailedLogToFile()
    {
        $logFile = storage_path('logs/subjects_fix_FINAL_' . now()->format('Y-m-d_H-i-s') . '.json');
        file_put_contents($logFile, json_encode($this->detailedLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->command->info("\n💾 Detailed log saved to: {$logFile}");
    }

    private function extractRowData($row)
    {
        return [
            'inn' => trim($row[$this->columnMap['inn']] ?? ''),
            'pinfl' => trim($row[$this->columnMap['pinfl']] ?? ''),
            'companyName' => trim($row[$this->columnMap['company_name']] ?? ''),
            'contractNumber' => trim($row[$this->columnMap['contract_number']] ?? ''),
            'contractDate' => $this->parseExcelDate($row[$this->columnMap['contract_date']] ?? ''),
            'completionDate' => $this->parseExcelDate($row[$this->columnMap['completion_date']] ?? ''),
        ];
    }

    private function isEmptyRow($row)
    {
        foreach ($row as $cell) {
            if (!empty(trim($cell))) {
                return false;
            }
        }
        return true;
    }

    private function findHeaderRow($data)
    {
        for ($i = 0; $i < min(10, count($data)); $i++) {
            $row = $data[$i];
            $score = 0;

            foreach ($row as $cell) {
                $cellText = strtolower(trim($cell));
                if (stripos($cellText, 'инн') !== false ||
                    stripos($cellText, 'пинфл') !== false ||
                    stripos($cellText, 'корхона') !== false ||
                    stripos($cellText, 'шарт') !== false) {
                    $score++;
                }
            }

            if ($score >= 2) {
                return $i;
            }
        }
        return 0;
    }

    private function parseExcelDate($value)
    {
        if (empty($value) || $value === '-') return null;

        try {
            // Excel serial number
            if (is_numeric($value) && $value > 1 && $value < 100000) {
                $excelEpoch = Carbon::create(1900, 1, 1);
                return $excelEpoch->addDays((int)$value - 2);
            }

            // M/D/YYYY format
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value)) {
                return Carbon::createFromFormat('n/j/Y', $value);
            }

            // DD.MM.YYYY format
            if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $value)) {
                return Carbon::createFromFormat('d.m.Y', $value);
            }

            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    // XLSX Reading Methods
    private function readXlsx($filePath)
    {
        $zip = new ZipArchive;
        if ($zip->open($filePath) !== TRUE) {
            throw new \Exception('Cannot open XLSX file');
        }

        try {
            $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($sharedStringsXml !== false) {
                $this->parseSharedStrings($sharedStringsXml);
            }

            $worksheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($worksheetXml === false) {
                throw new \Exception('Cannot read worksheet');
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
            $this->sharedStrings = [];
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
            $data[] = $this->parseWorksheetRow($row);
        }

        return $data;
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
                $cells[$colIndex] = $this->parseCellValue($cell);
            }
        }

        for ($i = 0; $i <= max(10, $maxCol); $i++) {
            $rowData[] = isset($cells[$i]) ? $cells[$i] : '';
        }

        return $rowData;
    }

    private function parseCellValue($cell)
    {
        if (isset($cell->v)) {
            $cellValue = (string)$cell->v;
            $cellType = isset($cell['t']) ? (string)$cell['t'] : '';

            if ($cellType === 's') {
                $stringIndex = (int)$cellValue;
                return isset($this->sharedStrings[$stringIndex]) ? $this->sharedStrings[$stringIndex] : '';
            }
            return $cellValue;
        }

        if (isset($cell->is->t)) {
            return (string)$cell->is->t;
        }

        return '';
    }

    private function columnIndexFromString($cellRef)
    {
        preg_match('/^([A-Z]+)/', $cellRef, $matches);
        if (!isset($matches[1])) return 0;

        $column = $matches[1];
        $index = 0;
        $length = strlen($column);

        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($column[$i]) - ord('A') + 1);
        }

        return $index - 1;
    }
}

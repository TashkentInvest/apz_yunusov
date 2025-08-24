<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\District;
use ZipArchive;
use SimpleXMLElement;

class AnalyzeExcel extends Command
{
    protected $signature = 'analyze:excel {file=apz_data —test.xlsx}';
    protected $description = 'Analyze Excel file for potential import issues without importing';
    
    private $sharedStrings = [];

    public function handle()
    {
        $fileName = $this->argument('file');
        $filePath = public_path($fileName);
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info("🔍 ANALYZING EXCEL FILE: {$fileName}");
        $this->info(str_repeat("=", 80));

        try {
            $data = $this->readXlsx($filePath);
            
            if (empty($data)) {
                $this->error('No data found in XLSX file');
                return 1;
            }

            // Remove header row
            if (count($data) > 0 && $this->isHeaderRow($data[0])) {
                array_shift($data);
                $this->info("📋 Header row detected and excluded from analysis");
            }

            $this->analyzeData($data);
            
        } catch (\Exception $e) {
            $this->error('Failed to read Excel file: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function analyzeData($data)
    {
        $totalRows = count($data);
        $this->info("📊 Total data rows: {$totalRows}");
        $this->info("");

        // Get available districts
        $availableDistricts = District::where('is_active', true)->pluck('name_ru')->toArray();
        
        // Analysis arrays
        $problemRows = [
            'missing_company' => [],
            'missing_inn_pinfl' => [],
            'invalid_amount' => [],
            'unmapped_districts' => [],
            'ref_errors' => [],
            'summary_rows' => [],
            'empty_rows' => []
        ];
        
        $districtCounts = [];
        $validRows = 0;

        foreach ($data as $index => $row) {
            $rowNumber = $index + 2; // +2 for Excel row number (header removed + Excel starts at 1)
            
            // Parse essential fields
            $inn = trim($row[3] ?? '');
            $pinfl = trim($row[4] ?? '');
            $companyName = trim($row[5] ?? '');
            $contractNumber = trim($row[6] ?? '');
            $districtName = trim($row[13] ?? '');
            $contractAmount = $this->parseAmount($row[15] ?? 0);
            
            // Check for empty rows
            if ($this->isEmptyRow($row)) {
                $problemRows['empty_rows'][] = $rowNumber;
                continue;
            }
            
            // Check for summary rows
            if ($this->isSummaryRow($row)) {
                $problemRows['summary_rows'][] = [
                    'row' => $rowNumber,
                    'content' => $companyName
                ];
                continue;
            }
            
            // Check for #REF! errors
            if (stripos($inn, '#REF') !== false || stripos($companyName, '#REF') !== false) {
                $problemRows['ref_errors'][] = [
                    'row' => $rowNumber,
                    'company' => $companyName,
                    'inn' => $inn
                ];
                continue;
            }
            
            // Check missing company name
            if (empty($companyName)) {
                $problemRows['missing_company'][] = [
                    'row' => $rowNumber,
                    'inn' => $inn,
                    'pinfl' => $pinfl,
                    'contract' => $contractNumber
                ];
                continue;
            }
            
            // Check missing INN and PINFL
            if (empty($inn) && empty($pinfl)) {
                $problemRows['missing_inn_pinfl'][] = [
                    'row' => $rowNumber,
                    'company' => $companyName,
                    'contract' => $contractNumber
                ];
                continue;
            }
            
            // Check invalid amount
            if ($contractAmount <= 0) {
                $problemRows['invalid_amount'][] = [
                    'row' => $rowNumber,
                    'company' => $companyName,
                    'amount' => $row[15] ?? 'N/A'
                ];
                continue;
            }
            
            // Check unmapped districts
            if (!empty($districtName) && !in_array($districtName, $availableDistricts)) {
                if (!isset($problemRows['unmapped_districts'][$districtName])) {
                    $problemRows['unmapped_districts'][$districtName] = [];
                }
                $problemRows['unmapped_districts'][$districtName][] = [
                    'row' => $rowNumber,
                    'company' => $companyName
                ];
            }
            
            // Count districts
            if (!empty($districtName)) {
                if (!isset($districtCounts[$districtName])) {
                    $districtCounts[$districtName] = 0;
                }
                $districtCounts[$districtName]++;
            }
            
            $validRows++;
        }

        $this->showAnalysisResults($problemRows, $districtCounts, $validRows, $totalRows, $availableDistricts);
    }

    private function showAnalysisResults($problemRows, $districtCounts, $validRows, $totalRows, $availableDistricts)
    {
        // Summary
        $this->info("📈 ANALYSIS SUMMARY:");
        $this->info("   ✅ Valid rows: {$validRows}");
        $this->info("   ❌ Problem rows: " . ($totalRows - $validRows));
        $this->info("");

        // Show problems
        foreach ($problemRows as $problemType => $rows) {
            if (empty($rows)) continue;
            
            $count = is_array($rows) && isset($rows[0]['row']) ? count($rows) : 
                    (is_array($rows) && !isset($rows[0]['row']) ? array_sum(array_map('count', $rows)) : count($rows));
            
            switch ($problemType) {
                case 'missing_company':
                    $this->warn("❌ MISSING COMPANY NAME ({$count} rows):");
                    foreach (array_slice($rows, 0, 10) as $row) {
                        $this->line("   Row {$row['row']}: INN={$row['inn']}, PINFL={$row['pinfl']}, Contract={$row['contract']}");
                    }
                    break;
                    
                case 'missing_inn_pinfl':
                    $this->warn("❌ MISSING INN AND PINFL ({$count} rows):");
                    foreach (array_slice($rows, 0, 10) as $row) {
                        $this->line("   Row {$row['row']}: {$row['company']} | Contract: {$row['contract']}");
                    }
                    break;
                    
                case 'invalid_amount':
                    $this->warn("❌ INVALID CONTRACT AMOUNT ({$count} rows):");
                    foreach (array_slice($rows, 0, 10) as $row) {
                        $this->line("   Row {$row['row']}: {$row['company']} | Amount: {$row['amount']}");
                    }
                    break;
                    
                case 'unmapped_districts':
                    $this->warn("⚠️  UNMAPPED DISTRICTS:");
                    foreach ($rows as $districtName => $districtRows) {
                        $rowNumbers = array_column($districtRows, 'row');
                        $rowList = implode(', ', array_slice($rowNumbers, 0, 10));
                        if (count($rowNumbers) > 10) {
                            $rowList .= '... +' . (count($rowNumbers) - 10) . ' more';
                        }
                        $this->line("   📍 '{$districtName}' in rows: {$rowList}");
                    }
                    break;
                    
                case 'ref_errors':
                    if (!empty($rows)) {
                        $this->warn("❌ #REF! ERRORS ({$count} rows):");
                        foreach (array_slice($rows, 0, 5) as $row) {
                            $this->line("   Row {$row['row']}: {$row['company']}");
                        }
                    }
                    break;
                    
                case 'summary_rows':
                    if (!empty($rows)) {
                        $this->info("ℹ️  SUMMARY ROWS ({$count} rows) - These will be skipped:");
                        foreach ($rows as $row) {
                            $this->line("   Row {$row['row']}: {$row['content']}");
                        }
                    }
                    break;
                    
                case 'empty_rows':
                    if (!empty($rows)) {
                        $this->info("ℹ️  EMPTY ROWS ({$count} rows) - These will be skipped");
                    }
                    break;
            }
            
            if ($count > 10 && $problemType !== 'unmapped_districts') {
                $this->line("   ... and " . ($count - 10) . " more rows");
            }
            $this->info("");
        }

        // District distribution
        if (!empty($districtCounts)) {
            $this->info("📍 DISTRICT DISTRIBUTION:");
            arsort($districtCounts);
            foreach ($districtCounts as $district => $count) {
                $status = in_array($district, $availableDistricts) ? '✅' : '❌';
                $this->line("   {$status} {$district}: {$count} contracts");
            }
            $this->info("");
        }

        // Available districts
        $this->info("📍 AVAILABLE DISTRICTS IN DATABASE:");
        $this->line("   " . implode(', ', $availableDistricts));
        $this->info("");

        // Recommendations
        $this->info("💡 RECOMMENDATIONS:");
        if (!empty($problemRows['unmapped_districts'])) {
            $this->info("   1. Add missing districts to database or update Excel with correct names");
        }
        if (!empty($problemRows['missing_company'])) {
            $this->info("   2. Add company names to rows missing this data");
        }
        if (!empty($problemRows['invalid_amount'])) {
            $this->info("   3. Fix contract amounts (should be > 0)");
        }
        if (!empty($problemRows['missing_inn_pinfl'])) {
            $this->info("   4. Add INN or PINFL to rows missing both identifiers");
        }
        
        $this->info("\n🚀 After fixing these issues, run: php artisan db:seed --class=ApzDataSeeder");
    }

    // Helper methods (same as in seeder)
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
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use ZipArchive;
use SimpleXMLElement;

class DebugApzDataSeeder extends Seeder
{
    private $sharedStrings = [];
    
    public function run()
    {
        $filePath = public_path('apz_data.xlsx');
        
        if (!file_exists($filePath)) {
            $this->command->error('APZ data file not found: ' . $filePath);
            return;
        }

        $this->command->info('=== DEBUG: Analyzing XLSX structure ===');

        try {
            $data = $this->readXlsx($filePath);
            
            if (empty($data)) {
                $this->command->error('No data found in XLSX file');
                return;
            }

            $this->command->info('Total rows found: ' . count($data));
            $this->command->info('Columns in first row: ' . count($data[0]));

            // Show first few rows
            $this->command->info("\n=== FIRST 5 ROWS ===");
            for ($i = 0; $i < min(5, count($data)); $i++) {
                $this->command->info("Row " . ($i + 1) . ":");
                for ($j = 0; $j < min(25, count($data[$i])); $j++) {
                    $value = $data[$i][$j] ?? '';
                    if (strlen($value) > 50) {
                        $value = substr($value, 0, 50) . '...';
                    }
                    $this->command->info("  Column " . chr(65 + $j) . " ({$j}): '{$value}'");
                }
                $this->command->info("---");
            }

            // Check for header row
            $this->command->info("\n=== HEADER DETECTION ===");
            if ($this->isHeaderRow($data[0])) {
                $this->command->info("First row appears to be a header row");
                $headerData = array_shift($data);
                
                $this->command->info("Header columns:");
                for ($j = 0; $j < min(25, count($headerData)); $j++) {
                    $value = $headerData[$j] ?? '';
                    $this->command->info("  Column " . chr(65 + $j) . " ({$j}): '{$value}'");
                }
            } else {
                $this->command->info("First row does not appear to be a header");
            }

            // Show sample data rows
            $this->command->info("\n=== SAMPLE DATA ROWS (after header removal) ===");
            $dataRowsToShow = min(10, count($data));
            
            for ($i = 0; $i < $dataRowsToShow; $i++) {
                if ($this->isEmptyRow($data[$i])) {
                    $this->command->info("Row " . ($i + 1) . ": [EMPTY ROW]");
                    continue;
                }
                
                $this->command->info("Row " . ($i + 1) . ":");
                
                // Show key columns based on expected structure
                $keyColumns = [
                    1 => 'INN',
                    2 => 'PINFL', 
                    3 => 'Company Name',
                    4 => 'Contract Number',
                    5 => 'Contract Status',
                    6 => 'Contract Date',
                    11 => 'District',
                    12 => 'Contract Amount'
                ];
                
                foreach ($keyColumns as $colIndex => $colName) {
                    $value = $data[$i][$colIndex] ?? '';
                    if (strlen($value) > 100) {
                        $value = substr($value, 0, 100) . '...';
                    }
                    $this->command->info("  {$colName} (Col " . chr(65 + $colIndex) . "): '{$value}'");
                }
                
                $this->command->info("---");
            }

            // Analyze data patterns
            $this->command->info("\n=== DATA ANALYSIS ===");
            
            $innCount = 0;
            $pinflCount = 0;
            $companyNameCount = 0;
            $contractNumberCount = 0;
            $nonEmptyRows = 0;
            
            foreach ($data as $row) {
                if ($this->isEmptyRow($row)) continue;
                
                $nonEmptyRows++;
                if (!empty(trim($row[1] ?? ''))) $innCount++;
                if (!empty(trim($row[2] ?? ''))) $pinflCount++;
                if (!empty(trim($row[3] ?? ''))) $companyNameCount++;
                if (!empty(trim($row[4] ?? ''))) $contractNumberCount++;
            }
            
            $this->command->info("Non-empty rows: {$nonEmptyRows}");
            $this->command->info("Rows with INN (Column B): {$innCount}");
            $this->command->info("Rows with PINFL (Column C): {$pinflCount}");
            $this->command->info("Rows with Company Name (Column D): {$companyNameCount}");
            $this->command->info("Rows with Contract Number (Column E): {$contractNumberCount}");

            // Check for possible column offset
            $this->command->info("\n=== COLUMN OFFSET CHECK ===");
            $this->command->info("Checking if data might be offset by 1 column...");
            
            $altInnCount = 0;
            $altPinflCount = 0;
            $altCompanyNameCount = 0;
            
            foreach (array_slice($data, 0, 50) as $row) {
                if ($this->isEmptyRow($row)) continue;
                
                if (!empty(trim($row[0] ?? ''))) $altInnCount++;
                if (!empty(trim($row[1] ?? ''))) $altPinflCount++;
                if (!empty(trim($row[2] ?? ''))) $altCompanyNameCount++;
            }
            
            $this->command->info("Alternative mapping (offset by -1):");
            $this->command->info("  Column A as INN: {$altInnCount} non-empty");
            $this->command->info("  Column B as PINFL: {$altPinflCount} non-empty");
            $this->command->info("  Column C as Company: {$altCompanyNameCount} non-empty");

        } catch (\Exception $e) {
            $this->command->error('Debug failed: ' . $e->getMessage());
            $this->command->error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    private function readXlsx($filePath)
    {
        $zip = new ZipArchive;
        
        if ($zip->open($filePath) !== TRUE) {
            throw new \Exception('Cannot open XLSX file');
        }

        // Read shared strings
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $this->parseSharedStrings($sharedStringsXml);
        }

        // Read worksheet data
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
                // Handle rich text
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
            
            // First pass: determine max column and collect cell data
            $cells = [];
            if (isset($row->c)) {
                foreach ($row->c as $cell) {
                    $cellRef = (string)$cell['r'];
                    $colIndex = $this->columnIndexFromString($cellRef);
                    $maxCol = max($maxCol, $colIndex);
                    
                    $value = '';
                    if (isset($cell->v)) {
                        $cellValue = (string)$cell->v;
                        
                        // Check cell type
                        $cellType = isset($cell['t']) ? (string)$cell['t'] : '';
                        
                        if ($cellType === 's') {
                            // Shared string
                            $stringIndex = (int)$cellValue;
                            $value = isset($this->sharedStrings[$stringIndex]) ? $this->sharedStrings[$stringIndex] : '';
                        } elseif ($cellType === 'str') {
                            // Inline string
                            $value = $cellValue;
                        } elseif ($cellType === 'b') {
                            // Boolean
                            $value = $cellValue === '1' ? 'TRUE' : 'FALSE';
                        } else {
                            // Number or date
                            $value = $cellValue;
                        }
                    } elseif (isset($cell->is->t)) {
                        // Inline string
                        $value = (string)$cell->is->t;
                    }
                    
                    $cells[$colIndex] = $value;
                }
            }
            
            // Second pass: create row array with proper indexing
            for ($i = 0; $i <= $maxCol; $i++) {
                $rowData[] = isset($cells[$i]) ? $cells[$i] : '';
            }
            
            $data[] = $rowData;
        }
        
        return $data;
    }

    private function columnIndexFromString($cellRef)
    {
        // Extract column letters from cell reference (e.g., "A1" -> "A", "AB1" -> "AB")
        preg_match('/^([A-Z]+)/', $cellRef, $matches);
        $column = $matches[1];
        
        // Convert column letters to zero-based index
        $index = 0;
        $length = strlen($column);
        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($column[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    private function isHeaderRow($row)
    {
        // Check if first row contains header-like text
        $headerKeywords = ['ИНН', 'ПИНФЛ', 'Корхона', 'шарт', 'Contract', 'Номи', 'Компания', '№'];
        
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
        
        // Check if all cells are empty or contain only whitespace
        foreach ($row as $cell) {
            if (trim($cell) !== '') {
                return false;
            }
        }
        return true;
    }
}
<?php
// Create this as app/Console/Commands/CheckExcelMapping.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\District;
use App\Models\Contract;
use Illuminate\Support\Facades\DB;

class CheckExcelMapping extends Command
{
    protected $signature = 'check:excel-mapping';
    protected $description = 'Check Excel data mapping vs actual database';

    public function handle()
    {
        $this->info('=== EXCEL vs DATABASE MAPPING CHECK ===');
        
        // Your Excel data expectations
        $excelData = [
            'Олмазор' => 28,
            'Мирзо-Улуғбек' => 47,
            'Яккасарой' => 27,
            'Шайхонтохур' => 27,
            'Сергели' => 24,
            'Яшнобод' => 36,
            'Миробод' => 29,
            'Янгихаёт' => 14,
            'Юнусобод' => 24,
            'Чилонзор' => 18,
            'Учтепа' => 12,
            'Бектемир' => 9,
        ];
        
        // Database mapping
        $districtMapping = [
            'Олмазор' => 'Алмазарский',
            'Мирзо-Улуғбек' => 'Мирзо-Улугбекский', 
            'Яккасарой' => 'Яккасарайский',
            'Шайхонтохур' => 'Шайхантахурский',
            'Сергели' => 'Сергелийский',
            'Яшнобод' => 'Яшнабадский',
            'Юнусобод' => 'Юнусабадский',
            'Миробод' => 'Мирабадский',
            'Янгихаёт' => 'Алмазарский', // This should add to Алмазарский
            'Учтепа' => 'Учтепинский',
            'Чилонзор' => 'Чиланзарский',
            'Бектемир' => 'Бектемирский',
        ];
        
        // Get actual database counts
        $actualCounts = DB::table('contracts as c')
            ->join('objects as o', 'c.object_id', '=', 'o.id')
            ->join('districts as d', 'o.district_id', '=', 'd.id')
            ->select('d.name_ru', DB::raw('count(*) as contract_count'))
            ->where('c.is_active', true)
            ->groupBy('d.id', 'd.name_ru')
            ->pluck('contract_count', 'name_ru')
            ->toArray();
        
        $this->info("\nComparison (Excel Expected vs Database Actual):");
        $this->info("=" . str_repeat("=", 60));
        
        $totalExpected = 0;
        $totalActual = 0;
        
        // Group expected counts by database district
        $expectedByDbDistrict = [];
        foreach ($excelData as $excelDistrict => $expectedCount) {
            $dbDistrict = $districtMapping[$excelDistrict];
            if (!isset($expectedByDbDistrict[$dbDistrict])) {
                $expectedByDbDistrict[$dbDistrict] = 0;
            }
            $expectedByDbDistrict[$dbDistrict] += $expectedCount;
            $totalExpected += $expectedCount;
        }
        
        // Compare with actual
        foreach ($expectedByDbDistrict as $dbDistrict => $expectedCount) {
            $actualCount = $actualCounts[$dbDistrict] ?? 0;
            $totalActual += $actualCount;
            
            $status = $expectedCount == $actualCount ? '✅' : ($actualCount < $expectedCount ? '⚠️ ' : '📈');
            $this->line(sprintf(
                "%s %-20s Expected: %2d | Actual: %2d | Diff: %+d",
                $status,
                $dbDistrict,
                $expectedCount,
                $actualCount,
                $actualCount - $expectedCount
            ));
        }
        
        // Show districts with contracts but not in Excel
        $this->info("\nDistricts with contracts not in Excel data:");
        foreach ($actualCounts as $districtName => $count) {
            if (!in_array($districtName, array_values($districtMapping))) {
                $this->line("🆕 {$districtName}: {$count} contracts");
            }
        }
        
        $this->info("\n" . str_repeat("=", 60));
        $this->info("TOTALS - Expected: {$totalExpected} | Actual: {$totalActual} | Difference: " . ($totalActual - $totalExpected));
        
        if ($totalActual != $totalExpected) {
            $this->warn("\n⚠️  There's a mismatch! Some contracts may not have been imported correctly.");
            $this->info("💡 Possible reasons:");
            $this->info("   - Some rows were skipped during import (empty data, errors)");
            $this->info("   - Some rows were duplicates and not imported");
            $this->info("   - District mapping issues");
            $this->info("   - Contract status filtering (only active contracts counted)");
        } else {
            $this->info("\n✅ All contracts imported correctly!");
        }
        
        $this->info("\n=== END CHECK ===");
    }
}

// Register in app/Console/Kernel.php and run: php artisan check:excel-mapping
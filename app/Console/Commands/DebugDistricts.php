<?php
// Create this as app/Console/Commands/DebugDistricts.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\District;
use App\Models\Contract;
use Illuminate\Support\Facades\DB;

class DebugDistricts extends Command
{
    protected $signature = 'debug:districts';
    protected $description = 'Debug district data and contract distribution';

    public function handle()
    {
        $this->info('=== DISTRICT DEBUG INFO ===');
        
        // Show all districts in database
        $this->info("\n1. Districts in database:");
        $districts = District::where('is_active', true)->get();
        foreach ($districts as $district) {
            $this->line("ID: {$district->id} - {$district->name_ru}");
        }
        
        // Show contract distribution by district
        $this->info("\n2. Contracts by district:");
        $contractsByDistrict = DB::table('contracts as c')
            ->join('objects as o', 'c.object_id', '=', 'o.id')
            ->join('districts as d', 'o.district_id', '=', 'd.id')
            ->select('d.name_ru', DB::raw('count(*) as contract_count'))
            ->where('c.is_active', true)
            ->groupBy('d.id', 'd.name_ru')
            ->get();
            
        foreach ($contractsByDistrict as $item) {
            $this->line("{$item->name_ru}: {$item->contract_count} contracts");
        }
        
        // Show objects without district
        $this->info("\n3. Objects without valid district:");
        $objectsWithoutDistrict = DB::table('objects as o')
            ->leftJoin('districts as d', 'o.district_id', '=', 'd.id')
            ->whereNull('d.id')
            ->orWhere('d.is_active', false)
            ->count();
            
        $this->line("Objects without valid district: {$objectsWithoutDistrict}");
        
        // Show sample objects
        $this->info("\n4. Sample objects with districts:");
        $sampleObjects = DB::table('objects as o')
            ->join('districts as d', 'o.district_id', '=', 'd.id')
            ->select('o.id', 'o.address', 'd.name_ru')
            ->limit(10)
            ->get();
            
        foreach ($sampleObjects as $obj) {
            $this->line("Object {$obj->id}: {$obj->address} - District: {$obj->name_ru}");
        }
        
        $this->info("\n=== END DEBUG ===");
    }
}

// Register this command in app/Console/Kernel.php:
// protected $commands = [
//     Commands\DebugDistricts::class,
// ];

// Then run: php artisan debug:districts
<?php

namespace Database\Seeders;

use App\Models\ContractStatus;
use Illuminate\Database\Seeder;

class ContractStatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            [
                'name_uz' => 'Jarayon',
                'name_ru' => 'В процессе',
                'code' => 'process',
                'color' => 'warning',
                'is_active' => true,
            ],
            [
                'name_uz' => 'Amalda',
                'name_ru' => 'Активный',
                'code' => 'current',
                'color' => 'success',
                'is_active' => true,
            ],
            [
                'name_uz' => 'Bekor qilingan',
                'name_ru' => 'Отменён',
                'code' => 'canceled',
                'color' => 'danger',
                'is_active' => true,
            ],
            [
                'name_uz' => 'Muddati o\'tgan',
                'name_ru' => 'Просрочен',
                'code' => 'expired',
                'color' => 'danger',
                'is_active' => true,
            ],
        ];

        foreach ($statuses as $status) {
            ContractStatus::create($status);
        }

        $this->command->info('Contract statuses seeded successfully!');
    }
}

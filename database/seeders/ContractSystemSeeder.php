<?php

// database/seeders/ContractSystemSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\District;
use App\Models\ContractStatus;
use App\Models\OrgForm;
use App\Models\BaseCalculationAmount;
use App\Models\ObjectType;
use App\Models\ConstructionType;
use App\Models\TerritorialZone;
use App\Models\PermitType;
use App\Models\IssuingAuthority;

class ContractSystemSeeder extends Seeder
{
    public function run()
    {
        $this->seedDistricts();
        $this->seedContractStatuses();
        $this->seedOrgForms();
        $this->seedBaseCalculationAmounts();
        $this->seedObjectTypes();
        $this->seedConstructionTypes();
        $this->seedTerritorialZones();
        $this->seedPermitTypes();
        $this->seedIssuingAuthorities();
    }

    private function seedDistricts()
    {
        $districts = [
            ['name_uz' => 'Uchtepa tumani', 'name_ru' => 'Учтепинский', 'code' => '01'],
            ['name_uz' => 'Bektemir tumani', 'name_ru' => 'Бектемирский', 'code' => '02'],
            ['name_uz' => 'Chilonzor tumani', 'name_ru' => 'Чиланзарский', 'code' => '03'],
            ['name_uz' => 'Yashnobod tumani', 'name_ru' => 'Яшнабадский', 'code' => '04'],
            ['name_uz' => 'Yakkasaroy tumani', 'name_ru' => 'Яккасарайский', 'code' => '05'],
            ['name_uz' => 'Sergeli tumani', 'name_ru' => 'Сергелийский', 'code' => '06'],
            ['name_uz' => 'Yunusobod tumani', 'name_ru' => 'Юнусабадский', 'code' => '07'],
            ['name_uz' => 'Olmazar tumani', 'name_ru' => 'Олмазарский', 'code' => '08'],
            ['name_uz' => 'Mirzo Ulug\'bek tumani', 'name_ru' => 'Мирзо Улугбекский', 'code' => '09'],
            ['name_uz' => 'Shayxontohur tumani', 'name_ru' => 'Шайхантахурский', 'code' => '10'],
            ['name_uz' => 'Mirobod tumani', 'name_ru' => 'Мирабадский', 'code' => '11'],
            ['name_uz' => 'Yangihayot tumani', 'name_ru' => 'Янгихаётский', 'code' => '12'],
        ];

        foreach ($districts as $district) {
            District::updateOrCreate(
                ['code' => $district['code']],
                array_merge($district, ['is_active' => true])
            );
        }
    }

    private function seedContractStatuses()
    {
        $statuses = [
            ['name_uz' => 'Loyiha', 'name_ru' => 'Проект', 'code' => 'draft', 'color' => '#6b7280'],
            ['name_uz' => 'Faol', 'name_ru' => 'Активный', 'code' => 'active', 'color' => '#059669'],
            ['name_uz' => 'Kutilmoqda', 'name_ru' => 'В ожидании', 'code' => 'pending', 'color' => '#d97706'],
            ['name_uz' => 'Yakunlangan', 'name_ru' => 'Завершен', 'code' => 'completed', 'color' => '#2563eb'],
            ['name_uz' => 'Bekor qilingan', 'name_ru' => 'Отменен', 'code' => 'cancelled', 'color' => '#dc2626'],
            ['name_uz' => 'To\'xtatilgan', 'name_ru' => 'Приостановлен', 'code' => 'suspended', 'color' => '#7c3aed'],
        ];

        foreach ($statuses as $status) {
            ContractStatus::updateOrCreate(
                ['code' => $status['code']],
                array_merge($status, ['is_active' => true])
            );
        }
    }

    private function seedOrgForms()
    {
        $orgForms = [
            ['name_uz' => 'Mas\'uliyati cheklangan jamiyat', 'name_ru' => 'Общество с ограниченной ответственностью', 'code' => 'LLC'],
            ['name_uz' => 'Aksiyadorlik jamiyati', 'name_ru' => 'Акционерное общество', 'code' => 'JSC'],
            ['name_uz' => 'Davlat korxonasi', 'name_ru' => 'Государственное предприятие', 'code' => 'SE'],
            ['name_uz' => 'Xususiy tadbirkor', 'name_ru' => 'Частный предприниматель', 'code' => 'PE'],
            ['name_uz' => 'Oillaviy korxona', 'name_ru' => 'Семейное предприятие', 'code' => 'FE'],
            ['name_uz' => 'Mikrofirma', 'name_ru' => 'Микрофирма', 'code' => 'MF'],
        ];

        foreach ($orgForms as $orgForm) {
            OrgForm::updateOrCreate(
                ['code' => $orgForm['code']],
                array_merge($orgForm, ['is_active' => true])
            );
        }
    }

    private function seedBaseCalculationAmounts()
    {
        $amounts = [
            [
                'amount' => 412000,
                'effective_from' => '2024-01-01',
                'effective_to' => '2024-12-31',
                'description' => 'Базовая расчетная величина на 2024 год'
            ],
            [
                'amount' => 450000,
                'effective_from' => '2025-01-01',
                'effective_to' => '2025-12-31',
                'description' => 'Базовая расчетная величина на 2025 год'
            ],
        ];

        foreach ($amounts as $amount) {
            BaseCalculationAmount::updateOrCreate(
                [
                    'effective_from' => $amount['effective_from'],
                    'effective_to' => $amount['effective_to']
                ],
                array_merge($amount, ['is_active' => true])
            );
        }
    }

    private function seedObjectTypes()
    {
        $objectTypes = [
            [
                'name_uz' => 'Alohida turgan xususiy ijtimoiy infratuzilma va turizm obyektlari',
                'name_ru' => 'Отдельно стоящие частные объекты социальной инфраструктуры и туризма',
                'coefficient' => 0.5,
                'description' => 'Частные социальные и туристические объекты'
            ],
            [
                'name_uz' => 'Davlat ulushi 50% dan ortiq investitsiya loyihalari',
                'name_ru' => 'Инвестиционные проекты с долей государства свыше 50%',
                'coefficient' => 0.5,
                'description' => 'Проекты с государственным участием'
            ],
            [
                'name_uz' => 'Ishlab chiqarish korxonalari obyektlari',
                'name_ru' => 'Объекты производственных предприятий',
                'coefficient' => 0.5,
                'description' => 'Производственные объекты'
            ],
            [
                'name_uz' => 'Omborxonalar (har qavat uchun 2 metr balandlik)',
                'name_ru' => 'Склады (высота каждого этажа не более 2 метров)',
                'coefficient' => 0.5,
                'description' => 'Складские помещения низкой высоты'
            ],
            [
                'name_uz' => 'Boshqa obyektlar',
                'name_ru' => 'Прочие объекты',
                'coefficient' => 1.0,
                'description' => 'Объекты, не указанные в других категориях'
            ],
        ];

        foreach ($objectTypes as $index => $objectType) {
            ObjectType::updateOrCreate(
                ['name_ru' => $objectType['name_ru']],
                array_merge($objectType, ['is_active' => true, 'sort_order' => $index + 1])
            );
        }
    }

    private function seedConstructionTypes()
    {
        $constructionTypes = [
            [
                'name_uz' => 'Yangi kapital qurilish',
                'name_ru' => 'Новое капитальное строительство',
                'coefficient' => 1.0,
                'description' => 'Строительство новых объектов'
            ],
            [
                'name_uz' => 'Obyektni rekonstruksiya qilish',
                'name_ru' => 'Реконструкция объекта',
                'coefficient' => 1.0,
                'description' => 'Реконструкция с изменением объемов'
            ],
            [
                'name_uz' => 'Loyiha-smeta hujjatlari ekspertizasi talab etilmaydigan rekonstruksiya',
                'name_ru' => 'Реконструкция, не требующая экспертизы проектно-сметной документации',
                'coefficient' => 0.0,
                'description' => 'Простая реконструкция без экспертизы'
            ],
            [
                'name_uz' => 'Qurilish hajmini o\'zgartirmagan holda rekonstruksiya',
                'name_ru' => 'Реконструкция без изменения строительного объема',
                'coefficient' => 0.0,
                'description' => 'Реконструкция без изменения объемов'
            ],
        ];

        foreach ($constructionTypes as $index => $constructionType) {
            ConstructionType::updateOrCreate(
                ['name_ru' => $constructionType['name_ru']],
                array_merge($constructionType, ['is_active' => true, 'sort_order' => $index + 1])
            );
        }
    }

    private function seedTerritorialZones()
    {
        $zones = [
            [
                'name_uz' => '1-zona',
                'name_ru' => 'Зона 1',
                'coefficient' => 1.40,
                'description' => 'Центральная зона с высоким коэффициентом'
            ],
            [
                'name_uz' => '2-zona',
                'name_ru' => 'Зона 2',
                'coefficient' => 1.25,
                'description' => 'Зона повышенной значимости'
            ],
            [
                'name_uz' => '3-zona',
                'name_ru' => 'Зона 3',
                'coefficient' => 1.00,
                'description' => 'Стандартная зона'
            ],
            [
                'name_uz' => '4-zona',
                'name_ru' => 'Зона 4',
                'coefficient' => 0.75,
                'description' => 'Зона с пониженным коэффициентом'
            ],
            [
                'name_uz' => '5-zona',
                'name_ru' => 'Зона 5',
                'coefficient' => 0.50,
                'description' => 'Периферийная зона'
            ],
        ];

        foreach ($zones as $index => $zone) {
            TerritorialZone::updateOrCreate(
                ['name_ru' => $zone['name_ru']],
                array_merge($zone, ['is_active' => true, 'sort_order' => $index + 1])
            );
        }
    }

    private function seedPermitTypes()
    {
        $permitTypes = [
            [
                'name_uz' => 'Qurilish ruxsatnomasi',
                'name_ru' => 'Разрешение на строительство',
                'description' => 'Основное разрешение на строительство'
            ],
            [
                'name_uz' => 'Rekonstruksiya ruxsatnomasi',
                'name_ru' => 'Разрешение на реконструкцию',
                'description' => 'Разрешение на реконструкцию объектов'
            ],
            [
                'name_uz' => 'Ta\'mirlash ruxsatnomasi',
                'name_ru' => 'Разрешение на капитальный ремонт',
                'description' => 'Разрешение на капитальный ремонт'
            ],
            [
                'name_uz' => 'Loyihalash ruxsatnomasi',
                'name_ru' => 'Разрешение на проектирование',
                'description' => 'Разрешение на проектирование объектов'
            ],
        ];

        foreach ($permitTypes as $index => $permitType) {
            PermitType::updateOrCreate(
                ['name_ru' => $permitType['name_ru']],
                array_merge($permitType, ['is_active' => true, 'sort_order' => $index + 1])
            );
        }
    }

    private function seedIssuingAuthorities()
    {
        $authorities = [
            [
                'name_uz' => 'Toshkent shahar hokimligi',
                'name_ru' => 'Хокимият города Ташкента',
                'description' => 'Основной орган выдачи разрешений'
            ],
            [
                'name_uz' => 'Davlat arxitektura va qurilish nazorati qo\'mitasi',
                'name_ru' => 'Государственный комитет по архитектуре и строительному контролю',
                'description' => 'Государственный контрольный орган'
            ],
            [
                'name_uz' => 'Tumanlardagi hokimlik organlari',
                'name_ru' => 'Органы районных хокимиятов',
                'description' => 'Районные органы власти'
            ],
            [
                'name_uz' => 'Maxsus ixtisoslashgan tashkilotlar',
                'name_ru' => 'Специализированные организации',
                'description' => 'Специализированные уполномоченные организации'
            ],
        ];

        foreach ($authorities as $index => $authority) {
            IssuingAuthority::updateOrCreate(
                ['name_ru' => $authority['name_ru']],
                array_merge($authority, ['is_active' => true, 'sort_order' => $index + 1])
            );
        }
    }
}

// Create individual seeder files as well for modularity

// database/seeders/DistrictSeeder.php
class DistrictSeeder extends Seeder
{
    public function run()
    {
        (new ContractSystemSeeder())->seedDistricts();
    }
}

// database/seeders/ContractStatusSeeder.php
class ContractStatusSeeder extends Seeder
{
    public function run()
    {
        (new ContractSystemSeeder())->seedContractStatuses();
    }
}

// Run this seeder by adding to DatabaseSeeder.php:
/*
public function run()
{
    $this->call([
        ContractSystemSeeder::class,
    ]);
}
*/

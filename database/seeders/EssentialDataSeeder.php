<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\District;
use App\Models\ContractStatus;
use App\Models\OrgForm;
use App\Models\BaseCalculationAmount;
use App\Models\CancellationReason;
use App\Models\PermitType;
use App\Models\ConstructionType;
use App\Models\ObjectType;
use App\Models\TerritorialZone;
use App\Models\IssuingAuthority;

class EssentialDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Seeding essential reference data...');

        // Districts
        if (District::count() == 0) {
            $this->command->info('Creating districts...');
            District::insert([
                ['name_uz' => 'Олмазор', 'name_ru' => 'Олмазор', 'code' => 'ALM', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Бектемир', 'name_ru' => 'Бектемир', 'code' => 'BEK', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Мирзо Улуғбек', 'name_ru' => 'Мирзо Улуғбек', 'code' => 'MUB', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Мирабад', 'name_ru' => 'Мирабад', 'code' => 'MIR', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Сергели', 'name_ru' => 'Сергели', 'code' => 'SER', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Олмалиқ', 'name_ru' => 'Олмалиқ', 'code' => 'ALQ', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Учтепа', 'name_ru' => 'Учтепа', 'code' => 'UCH', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Юнусобод', 'name_ru' => 'Юнусобод', 'code' => 'YUN', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Яккасарой', 'name_ru' => 'Яккасарой', 'code' => 'YAK', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Чилонзор', 'name_ru' => 'Чилонзор', 'code' => 'CHI', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Шайхонтохур', 'name_ru' => 'Шайхонтохур', 'code' => 'SHA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Яшнобод', 'name_ru' => 'Яшнобод', 'code' => 'YAS', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Contract Statuses
        if (ContractStatus::count() == 0) {
            $this->command->info('Creating contract statuses...');
            ContractStatus::insert([
                ['name_uz' => 'Амал қилувчи', 'name_ru' => 'Амал қилувчи', 'code' => 'ACTIVE', 'color' => '#28a745', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Бекор қилинган', 'name_ru' => 'Бекор қилинган', 'code' => 'CANCELLED', 'color' => '#dc3545', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Якунланган', 'name_ru' => 'Якунланган', 'code' => 'COMPLETED', 'color' => '#007bff', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Тўхтатилган', 'name_ru' => 'Тўхтатилган', 'code' => 'SUSPENDED', 'color' => '#ffc107', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Янги', 'name_ru' => 'Янги', 'code' => 'NEW', 'color' => '#17a2b8', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Organization Forms
        if (OrgForm::count() == 0) {
            $this->command->info('Creating organization forms...');
            OrgForm::insert([
                ['name_uz' => 'Масъулияти чекланган жамият', 'name_ru' => 'МЧЖ', 'code' => 'МЧЖ', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Акциядорлик жамияти', 'name_ru' => 'АЖ', 'code' => 'АЖ', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Хусусий тадбиркор', 'name_ru' => 'ХТ', 'code' => 'ХТ', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'ООО', 'name_ru' => 'ООО', 'code' => 'ООО', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Base Calculation Amount
        if (BaseCalculationAmount::count() == 0) {
            $this->command->info('Creating base calculation amount...');
            BaseCalculationAmount::create([
                'amount' => 412000.00,
                'effective_from' => '2024-01-01',
                'is_current' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Territorial Zones
        if (TerritorialZone::count() == 0) {
            $this->command->info('Creating territorial zones...');
            TerritorialZone::insert([
                ['name_uz' => 'Марказий зона', 'name_ru' => 'Марказий зона', 'code' => 'CENTER', 'coefficient' => 1.50, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Шаҳар зонаси', 'name_ru' => 'Шаҳар зонаси', 'code' => 'CITY', 'coefficient' => 1.20, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Ривожланувчи зона', 'name_ru' => 'Ривожланувчи зона', 'code' => 'DEVELOP', 'coefficient' => 1.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Четки зона', 'name_ru' => 'Четки зона', 'code' => 'OUTSKIRT', 'coefficient' => 0.80, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Permit Types
        if (PermitType::count() == 0) {
            $this->command->info('Creating permit types...');
            PermitType::insert([
                ['name_uz' => 'Қурилиш рухсатномаси', 'name_ru' => 'Қурилиш рухсатномаси', 'code' => 'CONSTRUCTION', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Реконструкция рухсатномаси', 'name_ru' => 'Реконструкция рухсатномаси', 'code' => 'RECONSTRUCTION', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Капитал таъмир рухсатномаси', 'name_ru' => 'Капитал таъмир рухсатномаси', 'code' => 'CAPITAL_REPAIR', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Construction Types
        if (ConstructionType::count() == 0) {
            $this->command->info('Creating construction types...');
            ConstructionType::insert([
                ['name_uz' => 'Турар жой биноси', 'name_ru' => 'Турар жой биноси', 'code' => 'RESIDENTIAL', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Тижорат биноси', 'name_ru' => 'Тижорат биноси', 'code' => 'COMMERCIAL', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Саноат биноси', 'name_ru' => 'Саноат биноси', 'code' => 'INDUSTRIAL', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Ижтимоий бино', 'name_ru' => 'Ижтимоий бино', 'code' => 'SOCIAL', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Object Types
        if (ObjectType::count() == 0) {
            $this->command->info('Creating object types...');
            ObjectType::insert([
                ['name_uz' => 'Кўп қаватли уй', 'name_ru' => 'Кўп қаватли уй', 'code' => 'APARTMENT', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Индивидуал уй', 'name_ru' => 'Индивидуал уй', 'code' => 'HOUSE', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Тижорат маркази', 'name_ru' => 'Тижорат маркази', 'code' => 'MALL', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Офис биноси', 'name_ru' => 'Офис биноси', 'code' => 'OFFICE', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Завод', 'name_ru' => 'Завод', 'code' => 'FACTORY', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Issuing Authorities
        if (IssuingAuthority::count() == 0) {
            $this->command->info('Creating issuing authorities...');
            IssuingAuthority::insert([
                ['name_uz' => 'Тошкент шаҳар қурилиш бош бошқармаси', 'name_ru' => 'Тошкент шаҳар қурилиш бош бошқармаси', 'code' => 'GUBG', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Архитектура ва шаҳарсозлик кенгаши', 'name_ru' => 'Архитектура ва шаҳарсозлик кенгаши', 'code' => 'AGS', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Тошкент шаҳар ҳокимияти', 'name_ru' => 'Тошкент шаҳар ҳокимияти', 'code' => 'KHOK', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Cancellation Reasons
        if (CancellationReason::count() == 0) {
            $this->command->info('Creating cancellation reasons...');
            CancellationReason::insert([
                ['name_uz' => 'Компания расмий хат ёзди', 'name_ru' => 'Компания расмий хат ёзди', 'type' => 'company_request', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Ўз хоҳишига кўра', 'name_ru' => 'Ўз хоҳишига кўра', 'type' => 'self_wish', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Кенгаш рухсат бермаган', 'name_ru' => 'Кенгаш рухсат бермаган', 'type' => 'council_rejection', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name_uz' => 'Биз ўзимиз учун таклиф', 'name_ru' => 'Биз ўзимиз учун таклиф', 'type' => 'our_proposal', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $this->command->info('Essential data seeding completed!');
        $this->command->info('Districts: ' . District::count());
        $this->command->info('Contract Statuses: ' . ContractStatus::count());
        $this->command->info('Organization Forms: ' . OrgForm::count());
        $this->command->info('Base Calculation Amounts: ' . BaseCalculationAmount::count());
    }
}
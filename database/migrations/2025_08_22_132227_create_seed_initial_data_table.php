<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Ташкилий шакллар
        DB::table('org_forms')->insert([
            ['name_uz' => 'Масъулияти чекланган жамият', 'name_ru' => 'Общество с ограниченной ответственностью', 'code' => 'МЧЖ'],
            ['name_uz' => 'Акциядорлик жамияти', 'name_ru' => 'Акционерное общество', 'code' => 'АЖ'],
            ['name_uz' => 'Хусусий тадбиркор', 'name_ru' => 'Частный предприниматель', 'code' => 'ХТ'],
            ['name_uz' => 'Якка тартибдаги тадбиркор', 'name_ru' => 'Индивидуальный предприниматель', 'code' => 'ЯТТ'],
        ]);

        // Тумлар
        DB::table('districts')->insert([
            ['name_uz' => 'Олмазор', 'name_ru' => 'Алмазарский', 'code' => 'ALM'],
            ['name_uz' => 'Бектемир', 'name_ru' => 'Бектемирский', 'code' => 'BEK'],
            ['name_uz' => 'Мирзо Улуғбек', 'name_ru' => 'Мирзо-Улугбекский', 'code' => 'MUB'],
            ['name_uz' => 'Мирабад', 'name_ru' => 'Мирабадский', 'code' => 'MIR'],
            ['name_uz' => 'Сергели', 'name_ru' => 'Сергелийский', 'code' => 'SER'],
            ['name_uz' => 'Олмалиқ', 'name_ru' => 'Алмалыкский', 'code' => 'ALQ'],
            ['name_uz' => 'Учтепа', 'name_ru' => 'Учтепинский', 'code' => 'UCH'],
            ['name_uz' => 'Юнусобод', 'name_ru' => 'Юнусабадский', 'code' => 'YUN'],
            ['name_uz' => 'Яккасарой', 'name_ru' => 'Яккасарайский', 'code' => 'YAK'],
            ['name_uz' => 'Чилонзор', 'name_ru' => 'Чиланзарский', 'code' => 'CHI'],
            ['name_uz' => 'Шайхонтохур', 'name_ru' => 'Шайхантахурский', 'code' => 'SHA'],
            ['name_uz' => 'Яшнобод', 'name_ru' => 'Яшнабадский', 'code' => 'YAS'],
        ]);

        // Шартнома статуслари
        DB::table('contract_statuses')->insert([
            ['name_uz' => 'Амал қилувчи', 'name_ru' => 'Действующий', 'code' => 'ACTIVE', 'color' => '#28a745'],
            ['name_uz' => 'Бекор қилинган', 'name_ru' => 'Отмененный', 'code' => 'CANCELLED', 'color' => '#dc3545'],
            ['name_uz' => 'Якунланган', 'name_ru' => 'Завершенный', 'code' => 'COMPLETED', 'color' => '#007bff'],
            ['name_uz' => 'Тўхтатилган', 'name_ru' => 'Приостановленный', 'code' => 'SUSPENDED', 'color' => '#ffc107'],
            ['name_uz' => 'Янги', 'name_ru' => 'Новый', 'code' => 'NEW', 'color' => '#17a2b8'],
        ]);

        // Беkor қилиш сабаблари
        DB::table('cancellation_reasons')->insert([
            ['name_uz' => 'Компания расмий хат ёзди', 'name_ru' => 'Компания написала официальное письмо', 'type' => 'company_request'],
            ['name_uz' => 'Ўз хоҳишига кўра', 'name_ru' => 'По собственному желанию', 'type' => 'self_wish'],
            ['name_uz' => 'Кенгаш рухсат бермаган', 'name_ru' => 'Совет не дал разрешение', 'type' => 'council_rejection'],
            ['name_uz' => 'Биз ўзимиз учун таклиф', 'name_ru' => 'Наше предложение для отмены', 'type' => 'our_proposal'],
        ]);

        // Базавий ҳисоблаш миқдорлари
        DB::table('base_calculation_amounts')->insert([
            ['amount' => 375000.00, 'effective_from' => '2023-01-01', 'effective_to' => '2023-12-31', 'is_current' => false],
            ['amount' => 412000.00, 'effective_from' => '2024-01-01', 'effective_to' => null, 'is_current' => true],
        ]);

        // Ҳудудий зоналар коэффициентлар билан
        DB::table('territorial_zones')->insert([
            ['name_uz' => 'Марказий зона', 'name_ru' => 'Центральная зона', 'code' => 'CENTER', 'coefficient' => 1.50],
            ['name_uz' => 'Шаҳар зонаси', 'name_ru' => 'Городская зона', 'code' => 'CITY', 'coefficient' => 1.20],
            ['name_uz' => 'Ривожланувчи зона', 'name_ru' => 'Развивающаяся зона', 'code' => 'DEVELOP', 'coefficient' => 1.00],
            ['name_uz' => 'Четки зона', 'name_ru' => 'Окраинная зона', 'code' => 'OUTSKIRT', 'coefficient' => 0.80],
        ]);

        // Рухсатнома турлари
        DB::table('permit_types')->insert([
            ['name_uz' => 'Қурилиш рухсатномаси', 'name_ru' => 'Разрешение на строительство', 'code' => 'CONSTRUCTION'],
            ['name_uz' => 'Реконструкция рухсатномаси', 'name_ru' => 'Разрешение на реконструкцию', 'code' => 'RECONSTRUCTION'],
            ['name_uz' => 'Капитал таъмир рухсатномаси', 'name_ru' => 'Разрешение на капитальный ремонт', 'code' => 'CAPITAL_REPAIR'],
        ]);

        // Қурилиш турлари
        DB::table('construction_types')->insert([
            ['name_uz' => 'Турар жой биноси', 'name_ru' => 'Жилое здание', 'code' => 'RESIDENTIAL'],
            ['name_uz' => 'Тижорат биноси', 'name_ru' => 'Коммерческое здание', 'code' => 'COMMERCIAL'],
            ['name_uz' => 'Саноат биноси', 'name_ru' => 'Промышленное здание', 'code' => 'INDUSTRIAL'],
            ['name_uz' => 'Ижтимоий бино', 'name_ru' => 'Социальное здание', 'code' => 'SOCIAL'],
        ]);

        // Объект турлари
        DB::table('object_types')->insert([
            ['name_uz' => 'Кўп қаватли уй', 'name_ru' => 'Многоэтажный дом', 'code' => 'APARTMENT'],
            ['name_uz' => 'Индивидуал уй', 'name_ru' => 'Индивидуальный дом', 'code' => 'HOUSE'],
            ['name_uz' => 'Тижорат маркази', 'name_ru' => 'Торговый центр', 'code' => 'MALL'],
            ['name_uz' => 'Офис биноси', 'name_ru' => 'Офисное здание', 'code' => 'OFFICE'],
            ['name_uz' => 'Завод', 'name_ru' => 'Завод', 'code' => 'FACTORY'],
        ]);

        // Рухсат берувчи органлар
        DB::table('issuing_authorities')->insert([
            ['name_uz' => 'Тошкент шаҳар қурилиш бош бошқармаси', 'name_ru' => 'Главное управление строительства г. Ташкент', 'code' => 'GUBG'],
            ['name_uz' => 'Архитектура ва шаҳарсозлик кенгаши', 'name_ru' => 'Архитектурно-градостроительный совет', 'code' => 'AGS'],
            ['name_uz' => 'Тошкент шаҳар ҳокимияти', 'name_ru' => 'Хокимият г. Ташкент', 'code' => 'KHOK'],
        ]);
    }

    public function down()
    {
        DB::table('issuing_authorities')->truncate();
        DB::table('object_types')->truncate();
        DB::table('construction_types')->truncate();
        DB::table('permit_types')->truncate();
        DB::table('territorial_zones')->truncate();
        DB::table('base_calculation_amounts')->truncate();
        DB::table('cancellation_reasons')->truncate();
        DB::table('contract_statuses')->truncate();
        DB::table('districts')->truncate();
        DB::table('org_forms')->truncate();
    }
};

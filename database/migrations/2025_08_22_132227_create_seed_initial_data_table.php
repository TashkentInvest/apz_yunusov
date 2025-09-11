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




        // Рухсатнома турлари
        DB::table('permit_types')->insert([
            ['name_uz' => 'Қурилиш рухсатномаси', 'name_ru' => 'Разрешение на строительство', 'code' => 'CONSTRUCTION'],
            ['name_uz' => 'Реконструкция рухсатномаси', 'name_ru' => 'Разрешение на реконструкцию', 'code' => 'RECONSTRUCTION'],
            ['name_uz' => 'Капитал таъмир рухсатномаси', 'name_ru' => 'Разрешение на капитальный ремонт', 'code' => 'CAPITAL_REPAIR'],
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
        DB::table('cancellation_reasons')->truncate();
        DB::table('contract_statuses')->truncate();
        DB::table('districts')->truncate();
        DB::table('org_forms')->truncate();
    }
};

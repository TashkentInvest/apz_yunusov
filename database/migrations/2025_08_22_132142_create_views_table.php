<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Drop existing views first (if they exist)
        DB::statement('DROP VIEW IF EXISTS v_contracts_overview');
        DB::statement('DROP VIEW IF EXISTS v_debtors');

        // Шартномалар умумий кўриниши
        DB::statement("
            CREATE VIEW v_contracts_overview AS
            SELECT
                c.id,
                c.contract_number,
                c.contract_date,
                c.completion_date,
                cs.name_ru as status_name,
                cs.color as status_color,
                s.company_name,
                s.inn,
                s.pinfl,
                d.name_ru as district_name,
                o.address,
                c.total_amount,
                c.contract_volume,
                c.payment_type,
                COALESCE(SUM(ap.amount), 0) as paid_amount,
                (c.total_amount - COALESCE(SUM(ap.amount), 0)) as remaining_amount,
                ROUND((COALESCE(SUM(ap.amount), 0) / NULLIF(c.total_amount, 0) * 100), 2) as payment_percent
            FROM contracts c
            LEFT JOIN contract_statuses cs ON c.status_id = cs.id
            LEFT JOIN subjects s ON c.subject_id = s.id
            LEFT JOIN objects o ON c.object_id = o.id
            LEFT JOIN districts d ON o.district_id = d.id
            LEFT JOIN actual_payments ap ON c.id = ap.contract_id
            WHERE c.is_active = 1
            GROUP BY c.id, c.contract_number, c.contract_date, c.completion_date, 
                     cs.name_ru, cs.color, s.company_name, s.inn, s.pinfl, 
                     d.name_ru, o.address, c.total_amount, c.contract_volume, c.payment_type
        ");

        // Қарздорлар кўриниши
        DB::statement("
            CREATE VIEW v_debtors AS
            SELECT
                c.id as contract_id,
                c.contract_number,
                s.company_name,
                s.inn,
                s.pinfl,
                d.name_ru as district_name,
                c.total_amount,
                COALESCE(SUM(ap.amount), 0) as paid_amount,
                (c.total_amount - COALESCE(SUM(ap.amount), 0)) as debt_amount,
                ps.year,
                ps.quarter,
                ps.quarter_amount as scheduled_amount,
                COALESCE(ap_q.quarter_paid, 0) as quarter_paid,
                (ps.quarter_amount - COALESCE(ap_q.quarter_paid, 0)) as quarter_debt,
                CASE
                    WHEN ps.quarter_amount > COALESCE(ap_q.quarter_paid, 0)
                    THEN DATEDIFF(CURDATE(), LAST_DAY(STR_TO_DATE(CONCAT(ps.year, '-', ps.quarter * 3), '%Y-%m')))
                    ELSE 0
                END as overdue_days
            FROM contracts c
            JOIN payment_schedules ps ON c.id = ps.contract_id AND ps.is_active = 1
            LEFT JOIN subjects s ON c.subject_id = s.id
            LEFT JOIN objects o ON c.object_id = o.id
            LEFT JOIN districts d ON o.district_id = d.id
            LEFT JOIN actual_payments ap ON c.id = ap.contract_id
            LEFT JOIN (
                SELECT
                    contract_id,
                    year,
                    quarter,
                    SUM(amount) as quarter_paid
                FROM actual_payments
                GROUP BY contract_id, year, quarter
            ) ap_q ON c.id = ap_q.contract_id AND ps.year = ap_q.year AND ps.quarter = ap_q.quarter
            WHERE c.is_active = 1
            AND ps.quarter_amount > COALESCE(ap_q.quarter_paid, 0)
            GROUP BY c.id, c.contract_number, s.company_name, s.inn, s.pinfl, 
                     d.name_ru, c.total_amount, ps.year, ps.quarter, 
                     ps.quarter_amount, ap_q.quarter_paid
        ");
    }

    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS v_contracts_overview');
        DB::statement('DROP VIEW IF EXISTS v_debtors');
    }
};
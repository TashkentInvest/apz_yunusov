<?php
// create_users_table.php migration
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->enum('role', ['admin', 'manager', 'employee'])->default('employee');
                $table->boolean('is_active')->default(true);
                $table->string('phone')->nullable();
                $table->string('department')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // Seed users if table is empty
        if (User::count() == 0) {
            User::create([
                'name' => 'Администратор',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('teamdevs'),
                'role' => 'admin',
                'department' => 'IT отдел',
                'phone' => '+998333088099',
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            User::create([
                'name' => 'Элбек Убайдуллаев',
                'email' => 'e.ubaydullaev@tashkentinvest.com',
                'password' => Hash::make('87654321aA'),
                'role' => 'admin',
                'department' => 'Договоры',
                'phone' => '+998935311221',
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            User::create([
                'name' => 'Дилмурод Мирзаев',
                'email' => 'd.mirzaev@tashkentinvest.com',
                'password' => Hash::make('87654321ADd'),
                'role' => 'admin',
                'department' => 'Платежи',
                'phone' => '+998974140045',
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};

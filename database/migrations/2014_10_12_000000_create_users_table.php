<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
                $table->string('role')->default('user'); // admin, manager, user
                $table->boolean('is_active')->default(true);
                $table->rememberToken();
                $table->timestamps();
            });

            // Create default admin user
            DB::table('users')->insert([
                'name' => 'Администратор',
                'email' => 'admin@tashkentinvest.com',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Видаляємо settings_temp, якщо вона існує
        Schema::dropIfExists('settings_temp');

        // Створюємо нову таблицю з правильною структурою
        Schema::create('settings_temp', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->timestamps();
        });

        // Якщо таблиця settings існує, переносимо дані (якщо є)
        if (Schema::hasTable('settings')) {
            // Перевіряємо, чи є стовпець value
            $columns = Schema::getColumnListing('settings');
            if (in_array('value', $columns)) {
                DB::statement('INSERT INTO settings_temp (value, created_at, updated_at) SELECT value, created_at, updated_at FROM settings');
            }
        }

        // Видаляємо стару таблицю settings
        Schema::dropIfExists('settings');

        // Перейменовуємо settings_temp на settings
        Schema::rename('settings_temp', 'settings');

        // Додаємо початкове значення для delivery_cost, якщо потрібно
        if (!DB::table('settings')->where('key', 'delivery_cost')->exists()) {
            DB::table('settings')->insert([
                'key' => 'delivery_cost',
                'value' => '50',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Відновлюємо стару структуру для відкату
        Schema::dropIfExists('settings_temp');

        Schema::create('settings_temp', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::dropIfExists('settings');

        Schema::rename('settings_temp', 'settings');
    }
};

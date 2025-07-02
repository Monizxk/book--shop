<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();
            $table->boolean('hidden')->default(false);

            // Внешний ключ для родительской категории
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');

            // Индексы для быстрого поиска
            $table->index('parent_id');
            $table->index('name');

            // Составной индекс для быстрого поиска по родителю и имени
            $table->index(['parent_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

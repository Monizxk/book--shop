<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->json('images')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('in_stock')->default(true);
            $table->boolean('is_on_sale')->default(false);
            $table->boolean('is_on_way')->default(false);
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->boolean('hidden')->default(false);
});
    }

    public function down(): void {
        Schema::dropIfExists('products');
    }
};

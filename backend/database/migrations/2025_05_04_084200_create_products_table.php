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
            $table->decimal('price', 8, 2)->nullable();
            $table->boolean('in_stock')->default(true);
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();
});

    }

    public function down(): void {
        Schema::dropIfExists('products');
    }
};

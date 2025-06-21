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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Customer Information
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');

            // Delivery Information
            $table->enum('delivery_method', ['novaPoshta', 'ukrPoshta', 'selfPickup']);
            $table->string('city')->nullable();
            $table->string('post_office')->nullable();

            // Payment Information
            $table->enum('payment_method', ['cashOnDelivery', 'cardOnline']);
            $table->text('comment')->nullable();

            // Order Totals
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('delivery_cost', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            // Order Items (stored as JSON)
            $table->json('items');

            // Order Status
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])
                ->default('pending');

            // Order Number (unique identifier)
            $table->string('order_number')->unique();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

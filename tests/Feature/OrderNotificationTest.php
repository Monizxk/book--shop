<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Services\OrderNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;


class OrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_order_notification_service_sends_emails()
    {
        // Create test settings
        Setting::create(['key' => 'order_notification_email', 'value' => 'seller@test.com']);
        Setting::create(['key' => 'order_notification_enabled', 'value' => '1']);

        // Create test product
        $product = Product::factory()->create([
            'title' => 'Test Product',
            'price' => 100.00
        ]);

        // Create test order
        $order = Order::create([
            'full_name' => 'John Doe',
            'email' => 'customer@test.com',
            'phone' => '+380123456789',
            'delivery_method' => 'novaPoshta',
            'city' => 'Kyiv',
            'post_office' => 'Test Office',
            'payment_method' => 'cardOnline',
            'subtotal' => 100.00,
            'delivery_cost' => 50.00,
            'total' => 150.00,
            'status' => 'pending'
        ]);

        // Create order item
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 1,
            'price' => 100.00
        ]);

        // Test the notification service
        $results = OrderNotificationService::sendOrderConfirmation($order);

        // Assert results
        assert($results['customer_email_sent'] === true);
        assert($results['seller_email_sent'] === true);
        assert(empty($results['errors']));

        // Assert emails were sent
        Mail::assertSent(\App\Mail\OrderConfirmationMail::class, function ($mail) use ($order) {
            return $mail->hasTo($order->email) && !$mail->isForSeller;
        });

        Mail::assertSent(\App\Mail\OrderConfirmationMail::class, function ($mail) {
            return $mail->hasTo('seller@test.com') && $mail->isForSeller;
        });
    }

    public function test_order_notification_service_respects_disabled_setting()
    {
        // Create test settings with notifications disabled
        Setting::create(['key' => 'order_notification_email', 'value' => 'seller@test.com']);
        Setting::create(['key' => 'order_notification_enabled', 'value' => '0']);

        // Create test product
        $product = Product::factory()->create([
            'title' => 'Test Product',
            'price' => 100.00
        ]);

        // Create test order
        $order = Order::create([
            'full_name' => 'John Doe',
            'email' => 'customer@test.com',
            'phone' => '+380123456789',
            'delivery_method' => 'novaPoshta',
            'city' => 'Kyiv',
            'post_office' => 'Test Office',
            'payment_method' => 'cardOnline',
            'subtotal' => 100.00,
            'delivery_cost' => 50.00,
            'total' => 150.00,
            'status' => 'pending'
        ]);

        // Create order item
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 1,
            'price' => 100.00
        ]);

        // Test the notification service
        $results = OrderNotificationService::sendOrderConfirmation($order);

        // Assert results
        assert($results['customer_email_sent'] === true);
        assert($results['seller_email_sent'] === false);
        assert(empty($results['errors']));

        // Assert only customer email was sent
        Mail::assertSent(\App\Mail\OrderConfirmationMail::class, function ($mail) use ($order) {
            return $mail->hasTo($order->email) && !$mail->isForSeller;
        });

        Mail::assertNotSent(\App\Mail\OrderConfirmationMail::class, function ($mail) {
            return $mail->hasTo('seller@test.com');
        });
    }
} 
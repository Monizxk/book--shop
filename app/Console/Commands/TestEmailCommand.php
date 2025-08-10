<?php

namespace App\Console\Commands;

use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Services\OrderNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    protected $signature = 'test:email {email?}';
    protected $description = 'Test email functionality';

    public function handle()
    {
        $this->info('Testing email functionality...');
        
        // Check current mail configuration
        $this->info('Mail driver: ' . config('mail.default'));
        $this->info('Mail from address: ' . config('mail.from.address'));
        $this->info('Mail from name: ' . config('mail.from.name'));
        
        // Check email settings
        $this->info('Seller email: ' . OrderNotificationService::getSellerEmail());
        $this->info('Notifications enabled: ' . (OrderNotificationService::areSellerNotificationsEnabled() ? 'Yes' : 'No'));
        
        // Get test email from command argument or use default
        $testEmail = $this->argument('email') ?: 'test@example.com';
        
        // Create a test order
        $order = new Order();
        $order->id = 999;
        $order->order_number = 'TEST-ORDER-001';
        $order->email = $testEmail;
        $order->full_name = 'Test Customer';
        $order->phone = '+380123456789';
        $order->delivery_method = 'novaPoshta';
        $order->city = 'Kyiv';
        $order->post_office = 'Test Office';
        $order->payment_method = 'cardOnline';
        $order->subtotal = 100.00;
        $order->delivery_cost = 50.00;
        $order->total = 150.00;
        $order->status = 'pending';
        $order->created_at = now();
        
        $this->info('Sending test email to: ' . $testEmail);
        
        try {
            // Test direct mail sending
            Mail::to($testEmail)->send(new OrderConfirmationMail($order, false));
            $this->info('✓ Direct email sent successfully');
        } catch (\Exception $e) {
            $this->error('✗ Direct email failed: ' . $e->getMessage());
        }
        
        // Test service method
        $results = OrderNotificationService::sendOrderConfirmation($order);
        $this->info('Service results: ' . json_encode($results, JSON_PRETTY_PRINT));
        
        if ($results['customer_email_sent']) {
            $this->info('✓ Customer email sent via service');
        } else {
            $this->error('✗ Customer email failed via service');
        }
        
        if ($results['seller_email_sent']) {
            $this->info('✓ Seller email sent via service');
        } else {
            $this->error('✗ Seller email failed via service');
        }
        
        if (!empty($results['errors'])) {
            $this->error('Errors: ' . implode(', ', $results['errors']));
        }
        
        $this->info('Test completed. Check logs for more details.');
    }
} 
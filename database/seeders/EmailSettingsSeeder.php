<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class EmailSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Set default seller email (should be changed in admin panel)
        Setting::updateOrCreate(
            ['key' => 'order_notification_email'],
            ['value' => 'seller@yourshop.com']
        );

        // Enable notifications by default
        Setting::updateOrCreate(
            ['key' => 'order_notification_enabled'],
            ['value' => '1']
        );

        $this->command->info('Email notification settings seeded successfully.');
        $this->command->info('Please update the seller email address in the admin panel.');
    }
} 
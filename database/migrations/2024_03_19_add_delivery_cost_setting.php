<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    public function up()
    {
        // Add delivery cost setting if it doesn't exist
        Setting::setValue('delivery_cost', 250);
    }

    public function down()
    {
        // Remove delivery cost setting
        Setting::where('key', 'delivery_cost')->delete();
    }
}; 
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function index()
    {
        return response()->json(
            Setting::pluck('value', 'key')
        );
    }

    public function getDeliveryCost()
    {
        $cost = $this->getDeliveryCostValue();
        return response()->json([
            'delivery_cost' => $cost
        ]);
    }

    // Новий метод для внутрішнього використання
    public function getDeliveryCostValue()
    {
        $cost = Setting::getValue('delivery_cost', 250);
        Log::info('Delivery cost from settings: ' . $cost);
        return (float) $cost;
    }
}

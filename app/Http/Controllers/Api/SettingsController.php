<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

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
        $cost = Setting::getValue('delivery_cost', 50);
        return response()->json([
            'delivery_cost' => (float) $cost
        ]);
    }
}

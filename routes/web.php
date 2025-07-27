<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/orders/{order}/pdf', [OrderController::class, 'exportPdf'])->name('orders.pdf');

Route::get('/debug-mail', function() {
    return response()->json([
        'mail_driver' => config('mail.default'),
        'smtp_host' => config('mail.mailers.smtp.host'),
        'smtp_port' => config('mail.mailers.smtp.port'),
        'smtp_username' => config('mail.mailers.smtp.username'),
        'smtp_password' => substr(config('mail.mailers.smtp.password'), 0, 4) . '***',
        'from_address' => config('mail.from.address'),
    ]);
});


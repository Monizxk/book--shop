<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/orders/{order}/pdf', [OrderController::class, 'exportPdf'])->name('orders.pdf');


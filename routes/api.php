<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\Api\SettingsController;

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/depth/{depth}', [CategoryController::class, 'getByDepth']);
    Route::get('/level/{level}', [CategoryController::class, 'getByLevel']);
    Route::get('/flat', [CategoryController::class, 'getFlat']);
    Route::get('/{parentId}/children', [CategoryController::class, 'getChildren']);
    Route::get('/possible-parents/{excludeId?}', [CategoryController::class, 'getPossibleParents']);
    Route::get('/{categoryId}/breadcrumb', [CategoryController::class, 'getBreadcrumb']);
    Route::get('/stats', [CategoryController::class, 'getStats']);
    Route::get('/search', [CategoryController::class, 'search']);
});

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/search', [BookController::class, 'search']);
Route::get('/products/sale', [ProductController::class, 'sale']);
Route::get('/products/way', [ProductController::class, 'wayProducts']);
Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/settings', [SettingsController::class, 'index']);
Route::get('/settings/delivery-cost', [SettingsController::class, 'getDeliveryCost']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/settings/contacts', [SettingsController::class, 'getContacts']);
Route::get('/settings/delivery', [SettingsController::class, 'getDeliveryTexts']);
Route::get('/settings/payment', [SettingsController::class, 'getPaymentTexts']);

Route::get('/pdf/orders/{order}', [OrderController::class, 'exportPdf'])->name('orders.pdf');

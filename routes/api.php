<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\Api\SettingsController;



Route::get('/categories', [CategoryController::class, 'index']);
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

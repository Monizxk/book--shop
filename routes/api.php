<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\Api\SettingsController;



Route::prefix('categories')->group(function () {
    // Получить все категории с полной иерархией
    Route::get('/', [CategoryController::class, 'index']);

    // Получить категории с указанной глубиной (1-5)
    Route::get('/depth/{depth}', [CategoryController::class, 'getByDepth']);

    // Получить категории определенного уровня (1-5)
    Route::get('/level/{level}', [CategoryController::class, 'getByLevel']);

    // Получить плоский список всех категорий
    Route::get('/flat', [CategoryController::class, 'getFlat']);

    // Получить дочерние категории для родительской
    Route::get('/{parentId}/children', [CategoryController::class, 'getChildren']);

    // Получить возможных родителей (исключая указанную категорию)
    Route::get('/possible-parents/{excludeId?}', [CategoryController::class, 'getPossibleParents']);

    // Получить хлебные крошки для категории
    Route::get('/{categoryId}/breadcrumb', [CategoryController::class, 'getBreadcrumb']);

    // Получить статистику категорий
    Route::get('/stats', [CategoryController::class, 'getStats']);

    // Поиск категорий
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

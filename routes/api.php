<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\BookController;



Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/search', [BookController::class, 'search']);
Route::get('/products/sale', [ProductController::class, 'sale']);
Route::get('/products/{id}', [ProductController::class, 'show']);

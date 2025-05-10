<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all(); // Повертаємо всі продукти
    }

    public function show($id)
    {
        return Product::findOrFail($id); // Повертаємо продукт за його ID
    }
}


<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index()
    {
        return Product::where('hidden', false)->get(); // Повертаємо только не скрытые продукты
    }

    public function show($id)
    {
        return Product::where('id', $id)->where('hidden', false)->firstOrFail(); // Только не скрытый продукт
    }
    public function sale()
    {
        $products = Product::where('is_on_sale', true)->where('hidden', false)->get();
        return response()->json($products);
    }
    public function wayProducts()
    {
        return Product::where('is_on_way', true)->where('hidden', false)->get();
    }
}


<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $paginated = Product::where('hidden', false)->paginate($perPage);
        
        return response()->json([
            'products' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total()
        ]);
    }

    public function show($id)
    {
        return Product::where('id', $id)->where('hidden', false)->firstOrFail(); // Только не скрытый продукт
    }
    
    public function sale(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $paginated = Product::where('is_on_sale', true)->where('hidden', false)->paginate($perPage);
        
        return response()->json([
            'products' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total()
        ]);
    }
    
    public function wayProducts(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $paginated = Product::where('is_on_way', true)->where('hidden', false)->paginate($perPage);
        
        return response()->json([
            'products' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total()
        ]);
    }
}


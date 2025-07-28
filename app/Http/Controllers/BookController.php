<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return response()->json([
                'message' => 'Search query is required',
                'data' => []
            ], 400);
        }

        try {
            $products = Product::whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($query) . '%'])
                ->get();
            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error searching products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            $products = Product::all();
            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching products',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

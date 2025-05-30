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
        \Log::info('Search request received with query: ' . $query);

        if (empty($query)) {
            \Log::warning('Empty search query');
            return response()->json([
                'message' => 'Search query is required',
                'data' => []
            ], 400);
        }

        try {
            $products = Product::whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($query) . '%'])
                ->get();
            \Log::info('Found products: ' . $products->toJson());
            return response()->json($products);
        } catch (\Exception $e) {
            \Log::error('Search error: ' . $e->getMessage());
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
            \Log::info('Fetched all products: ' . $products->toJson());
            return response()->json($products);
        } catch (\Exception $e) {
            \Log::error('Fetch products error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching products',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

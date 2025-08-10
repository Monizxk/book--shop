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
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        if (empty($query)) {
            return response()->json([
                'message' => 'Search query is required',
                'data' => []
            ], 400);
        }

        try {
            $paginated = Product::with('category')
            ->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($query) . '%'])
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'products' => $paginated->items(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error searching products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $paginated = Product::with('category')
            ->paginate($perPage);

            return response()->json([
                'products' => $paginated->items(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching products',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

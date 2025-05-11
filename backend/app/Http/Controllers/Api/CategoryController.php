<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Получаем только корневые категории (языки)
        $categories = Category::whereNull('parent_id')
            ->with(['children.children']) // Загружаем два уровня дочерних категорий
            ->get();
        
        return response()->json($categories);
    }
    
    /**
     * Alternative method to get categories with single level of children
     *
     * @return \Illuminate\Http\Response
     */
    public function getSimpleCategories()
    {
        $categories = Category::with('children')->whereNull('parent_id')->get();
        return response()->json($categories);
    }
}
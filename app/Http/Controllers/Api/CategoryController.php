<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of all categories with full hierarchy.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Получаем только корневые категории с полной иерархией (5 уровней)
        $categories = Category::whereNull('parent_id')
            ->with([
                'children.children.children.children.children' // 5 уровней вложенности
            ])
            ->get();

        return response()->json($categories);
    }

    /**
     * Get categories with specified depth level
     *
     * @param int $depth Maximum depth level (1-5)
     * @return \Illuminate\Http\Response
     */
    public function getByDepth($depth = 2)
    {
        $depth = min(5, max(1, $depth)); // Ограничиваем от 1 до 5

        $with = [];
        $current = 'children';

        for ($i = 1; $i < $depth; $i++) {
            $with[] = $current;
            $current .= '.children';
        }

        if (!empty($with)) {
            $with[] = $current;
        }

        $categories = Category::whereNull('parent_id')
            ->with($with)
            ->get();

        return response()->json($categories);
    }

    /**
     * Get categories by specific level
     *
     * @param int $level Category level (1-5)
     * @return \Illuminate\Http\Response
     */
    public function getByLevel($level)
    {
        $level = min(5, max(1, $level)); // Ограничиваем от 1 до 5

        $categories = Category::byLevel($level)
            ->with('parent', 'children')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'level' => $category->getLevel(),
                    'full_path' => $category->getFullPath(),
                    'parent' => $category->parent,
                    'children_count' => $category->children->count(),
                    'can_have_children' => $category->canHaveChildren(),
                ];
            });

        return response()->json($categories);
    }

    /**
     * Get flat list of all categories with hierarchy info
     *
     * @return \Illuminate\Http\Response
     */
    public function getFlat()
    {
        $categories = Category::with('parent')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'parent_id' => $category->parent_id,
                    'level' => $category->getLevel(),
                    'full_path' => $category->getFullPath(),
                    'can_have_children' => $category->canHaveChildren(),
                    'children_count' => $category->children()->count(),
                ];
            });

        return response()->json($categories);
    }

    /**
     * Get category tree for specific parent
     *
     * @param int $parentId
     * @return \Illuminate\Http\Response
     */
    public function getChildren($parentId)
    {
        $parent = Category::with([
            'children.children.children.children.children'
        ])->findOrFail($parentId);

        return response()->json([
            'parent' => [
                'id' => $parent->id,
                'name' => $parent->name,
                'level' => $parent->getLevel(),
                'full_path' => $parent->getFullPath(),
            ],
            'children' => $parent->children,
        ]);
    }

    /**
     * Get possible parents for a category (levels 1-4 only)
     *
     * @param int|null $excludeId Category ID to exclude from results
     * @return \Illuminate\Http\Response
     */
    public function getPossibleParents($excludeId = null)
    {
        $categories = Category::getPossibleParents($excludeId)
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'level' => $category->getLevel(),
                    'full_path' => $category->getFullPath(),
                    'can_have_children' => $category->canHaveChildren(),
                ];
            });

        return response()->json($categories);
    }

    /**
     * Get category breadcrumb
     *
     * @param int $categoryId
     * @return \Illuminate\Http\Response
     */
    public function getBreadcrumb($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $ancestors = $category->ancestors();

        $breadcrumb = $ancestors->map(function ($ancestor) {
            return [
                'id' => $ancestor->id,
                'name' => $ancestor->name,
                'level' => $ancestor->getLevel(),
            ];
        });

        $breadcrumb->push([
            'id' => $category->id,
            'name' => $category->name,
            'level' => $category->getLevel(),
        ]);

        return response()->json($breadcrumb);
    }

    /**
     * Get category statistics
     *
     * @return \Illuminate\Http\Response
     */
    public function getStats()
    {
        $stats = [
            'total_categories' => Category::count(),
            'by_level' => [
                'level_1' => Category::byLevel(1)->count(),
                'level_2' => Category::byLevel(2)->count(),
                'level_3' => Category::byLevel(3)->count(),
                'level_4' => Category::byLevel(4)->count(),
                'level_5' => Category::byLevel(5)->count(),
            ],
            'can_have_children' => Category::getPossibleParents()->count(),
            'max_depth_reached' => Category::byLevel(5)->exists(),
        ];

        return response()->json($stats);
    }

    /**
     * Search categories
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $level = $request->get('level');
        $parentId = $request->get('parent_id');

        $categories = Category::where('name', 'like', "%{$query}%")
            ->when($level, function ($q) use ($level) {
                $q->byLevel($level);
            })
            ->when($parentId, function ($q) use ($parentId) {
                $q->where('parent_id', $parentId);
            })
            ->with('parent')
            ->limit(50)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'level' => $category->getLevel(),
                    'full_path' => $category->getFullPath(),
                    'parent' => $category->parent ? [
                        'id' => $category->parent->id,
                        'name' => $category->parent->name,
                    ] : null,
                ];
            });

        return response()->json($categories);
    }
}

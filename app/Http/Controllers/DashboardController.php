<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $selectedCategory = $request->input('category');

        $categories = Category::withCount(['products' => function ($q) {
            $q->where('is_active', true);
        }])->get();

        $totalProductsCount = Product::where('is_active', true)->count();

        $products = Product::with(['images', 'category'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('is_active', true)
            ->when($selectedCategory, function ($query) use ($selectedCategory) {
                $query->whereHas('category', function ($q) use ($selectedCategory) {
                    $q->where('slug', $selectedCategory);
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dashboard', [
            'products' => $products,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'totalProductsCount' => $totalProductsCount,
        ]);
    }

    public function search(Request $request)
    {
        $query = trim($request->input('query', ''));
        $selectedCategory = $request->input('category');

        $products = Product::with(['images', 'category'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('is_active', true)
            ->when($query !== '', function ($q) use ($query) {
                $term = '%' . strtolower($query) . '%';

                $q->where(function ($sub) use ($term) {
                    $sub->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(description) LIKE ?', [$term]);
                });
            })
            ->when($selectedCategory, function ($query) use ($selectedCategory) {
                $query->whereHas('category', function ($q) use ($selectedCategory) {
                    $q->where('slug', $selectedCategory);
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Search', [
            'products' => $products,
            'query' => $query,
        ]);
    }

    public function searchPreview(Request $request)
    {
        $query = trim($request->input('query', ''));

        if ($query === '') {
            return response()->json(['products' => []]);
        }

        $term = '%' . strtolower($query) . '%';

        $products = Product::with(['images', 'category'])
            ->where('is_active', true)
            ->where(function ($sub) use ($term) {
                $sub->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$term]);
            })
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'products' => $products,
        ]);
    }
}

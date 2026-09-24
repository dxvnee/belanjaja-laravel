<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $products = Product::with('images')
            ->where('is_active', true)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dashboard', [
            'products' => $products,
        ]);
    }

    public function search(Request $request)
    {
        $query = trim((string) ($request->query('query') ?? $request->query('q') ?? $request->input('query')));

        $products = Product::with('images')
            ->where('is_active', true)
            ->when($query !== '', function ($q) use ($query) {
                $term = '%' . strtolower($query) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(description) LIKE ?', [$term]);
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
}

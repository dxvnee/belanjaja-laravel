<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $products = Product::with('images')
            ->where('is_active', true)
            ->latest()
            ->get();

        return Inertia::render('Dashboard', [
            'products' => $products,
        ]);
    }
}

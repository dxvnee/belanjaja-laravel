<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        $products = Product::with('images')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return Inertia::render('Admin', [
            'products' => $products,
        ]);
    }
}

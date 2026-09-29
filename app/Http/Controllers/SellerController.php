<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SellerController extends Controller
{
    public function show($id)
    {
        $seller = User::withCount([
            'products' => function ($query) {
                $query->where('is_active', true);
            },
        ])->findOrFail($id);

        $sellerRating = Product::where('products.user_id', $seller->id)
            ->join('reviews', 'products.id', '=', 'reviews.product_id')
            ->avg('reviews.rating');

        $sellerReviewCount = Product::where('products.user_id', $seller->id)
            ->join('reviews', 'products.id', '=', 'reviews.product_id')
            ->count('reviews.id');

        $seller->avg_rating = $sellerRating ? round((float) $sellerRating, 1) : 0;
        $seller->reviews_count = $sellerReviewCount;

        $products = Product::where('user_id', $seller->id)
            ->where('is_active', true)
            ->with('images')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('SellerProfile', [
            'seller'   => $seller,
            'products' => $products,
        ]);
    }
}

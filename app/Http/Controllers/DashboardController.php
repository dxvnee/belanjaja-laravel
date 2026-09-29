<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $selectedCategory = $request->input('category');
        $activeFeed = $request->input('feed', 'for-you');

        // Check if user requested a reshuffle of the recommendations
        if ($request->has('refresh_feed')) {
            $request->session()->put('feed_seed', rand(1, 9999));
        }

        $user = Auth::user();
        $preferences = $this->getUserPreferences($user);
        $topCategoryIds = $preferences['preferred_categories'];
        $userCity = $preferences['user_city'];

        $categories = Category::withCount(['products' => function ($q) {
            $q->where('is_active', true);
        }])->get();

        $totalProductsCount = Product::where('is_active', true)->count();

        $query = Product::with(['images', 'category'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('is_active', true);

        // Apply category filter if selected
        if ($selectedCategory) {
            $query->whereHas('category', function ($q) use ($selectedCategory) {
                $q->where('slug', $selectedCategory);
            });
        }

        // Apply Feed Mode / Sorting Algorithm
        switch ($activeFeed) {
            case 'popular':
                $query->orderByDesc('reviews_count')
                      ->orderByDesc('reviews_avg_rating')
                      ->latest();
                break;

            case 'near-you':
                if (!empty($userCity)) {
                    $escapedCity = '%' . strtolower($userCity) . '%';
                    $query->orderByRaw("CASE WHEN LOWER(location) LIKE ? THEN 1 ELSE 2 END", [$escapedCity])
                          ->latest();
                } else {
                    $query->latest();
                }
                break;

            case 'latest':
                $query->latest();
                break;

            case 'for-you':
            default:
                $scoreParts = [];
                $bindings = [];

                // 1. Boost for user's favorite / frequently purchased categories
                if (!empty($topCategoryIds)) {
                    $catPlaceholders = implode(',', array_fill(0, count($topCategoryIds), '?'));
                    $scoreParts[] = "CASE WHEN category_id IN ($catPlaceholders) THEN 30 ELSE 0 END";
                    $bindings = array_merge($bindings, $topCategoryIds);
                }

                // 2. Boost for same city / location
                if (!empty($userCity)) {
                    $scoreParts[] = "CASE WHEN LOWER(location) LIKE ? THEN 20 ELSE 0 END";
                    $bindings[] = '%' . strtolower($userCity) . '%';
                }

                // 3. Boost for in-stock products
                $scoreParts[] = "CASE WHEN stock > 0 THEN 10 ELSE 0 END";

                // 4. Boost for high ratings
                $scoreParts[] = "CASE WHEN (SELECT AVG(rating) FROM reviews WHERE reviews.product_id = products.id) >= 4 THEN 10 ELSE 0 END";

                // 5. Serendipity / Randomization factor (deterministic per session seed for stable pagination)
                // Database-agnostic formula (compatible with PostgreSQL, MySQL, SQLite)
                $seed = (int) ($request->session()->get('feed_seed') ?: rand(1, 9999));
                if ($seed <= 0) {
                    $seed = rand(1, 9999);
                }
                $request->session()->put('feed_seed', $seed);

                $scoreParts[] = "ABS((products.id * $seed) % 15)";

                $scoreSql = '(' . implode(' + ', $scoreParts) . ')';
                $query->orderByRaw("$scoreSql DESC", $bindings)->latest();
                break;
        }

        $products = $query->paginate(10)->withQueryString();

        // Attach recommendation badges to items in current page
        $products->getCollection()->transform(function ($product) use ($topCategoryIds, $userCity) {
            $badge = null;
            if (!empty($userCity) && !empty($product->location) && stripos($product->location, $userCity) !== false) {
                $badge = '📍 Dekat Kotamu';
            } elseif (!empty($topCategoryIds) && in_array($product->category_id, $topCategoryIds)) {
                $badge = '✨ Sesuai Minatmu';
            } elseif ($product->reviews_avg_rating >= 4.5 && $product->reviews_count >= 1) {
                $badge = '⭐ Rating Tinggi';
            } elseif ($product->reviews_count >= 3) {
                $badge = '🔥 Populer';
            }

            $product->recommendation_badge = $badge;
            return $product;
        });

        return Inertia::render('Dashboard', [
            'products' => $products,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'totalProductsCount' => $totalProductsCount,
            'activeFeed' => $activeFeed,
            'userCity' => $userCity,
            'hasHistory' => $preferences['has_history'],
        ]);
    }

    protected function getUserPreferences($user): array
    {
        if (!$user) {
            return [
                'preferred_categories' => [],
                'user_city' => null,
                'has_history' => false,
            ];
        }

        $userId = $user->id;

        // Categories of products bought by user
        $purchasedCategoryIds = Product::whereHas('orderItems.order', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('status', '!=', 'cancelled');
        })->pluck('category_id')->filter()->all();

        // Categories of products positively reviewed
        $likedCategoryIds = Product::whereHas('reviews', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('rating', '>=', 4);
        })->pluck('category_id')->filter()->all();

        // Categories of items in cart
        $cartCategoryIds = Product::whereHas('cartItems.cart', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->pluck('category_id')->filter()->all();

        // Combine and weigh signals
        $allCategorySignals = array_merge(
            $purchasedCategoryIds,
            $purchasedCategoryIds, // 2x weight for actual completed orders
            $likedCategoryIds,
            $cartCategoryIds
        );

        $categoryCounts = array_count_values($allCategorySignals);
        arsort($categoryCounts);
        $topCategoryIds = array_keys(array_slice($categoryCounts, 0, 3, true));

        $userCity = $user->address()->first()?->city;

        return [
            'preferred_categories' => $topCategoryIds,
            'user_city' => $userCity,
            'has_history' => !empty($allCategorySignals) || !empty($userCity),
        ];
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

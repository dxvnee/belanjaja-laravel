<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // 1. Seller statistics
        $totalRevenue = OrderItem::whereHas('product', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->whereHas('order', function ($q) {
            $q->whereIn('status', ['paid', 'shipped', 'completed']);
        })->sum('subtotal');

        $ordersQuery = Order::whereHas('items.product', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });

        $totalOrders = (clone $ordersQuery)->count();
        $pendingShipment = (clone $ordersQuery)->where('status', 'paid')->count();
        $shippedOrders = (clone $ordersQuery)->where('status', 'shipped')->count();
        $completedOrders = (clone $ordersQuery)->where('status', 'completed')->count();

        $productsQuery = Product::where('user_id', $userId);
        $totalProducts = (clone $productsQuery)->count();
        $activeProducts = (clone $productsQuery)->where('is_active', true)->where('stock', '>', 0)->count();
        $outOfStockProducts = (clone $productsQuery)->where('stock', '<=', 0)->count();

        // 2. Recent incoming orders for seller (up to 5)
        $recentOrders = (clone $ordersQuery)
            ->with(['user', 'items.product.images'])
            ->latest()
            ->take(5)
            ->get();

        // 3. Products list with multi-category relations
        $products = Product::with(['images', 'category', 'categories'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return Inertia::render('Admin', [
            'stats' => [
                'total_revenue'         => (float) $totalRevenue,
                'total_orders'          => $totalOrders,
                'pending_shipment'      => $pendingShipment,
                'shipped_orders'        => $shippedOrders,
                'completed_orders'      => $completedOrders,
                'total_products'        => $totalProducts,
                'active_products'       => $activeProducts,
                'out_of_stock_products' => $outOfStockProducts,
            ],
            'recent_orders' => $recentOrders,
            'products'      => $products,
        ]);
    }

    public function orders(Request $request)
    {
        $userId = Auth::id();

        $orders = Order::whereHas('items.product', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with(['user', 'items.product.images'])
        ->latest()
        ->paginate(10)
        ->withQueryString();

        return Inertia::render('Orders/SellerOrders', [
            'orders' => $orders,
        ]);
    }

    public function shipOrder(Request $request, $id)
    {
        $userId = Auth::id();

        $order = Order::whereHas('items.product', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->findOrFail($id);

        $validated = $request->validate([
            'tracking_number' => ['required', 'string', 'max:100'],
        ]);

        $order->update([
            'status'          => 'shipped',
            'tracking_number' => $validated['tracking_number'],
        ]);

        return back()->with('success', 'Pesanan berhasil dikirim!');
    }
}

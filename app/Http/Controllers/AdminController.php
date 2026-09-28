<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
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

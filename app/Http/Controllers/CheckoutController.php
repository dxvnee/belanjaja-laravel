<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $product_ids = $request->input('product_ids', []);

        $cart = $user->cart;

        $products = $cart
            ? CartItem::where('cart_id', $cart->id)
            ->whereIn('product_id', $product_ids)
            ->with('product.images')
            ->get()
            : collect();

        $address = Address::where('user_id',$user->id)
            ->get()
            ->toArray();

        return Inertia::render('Checkout', [
            'title' => 'Checkout',
            'products' => $products,
            'addresses' => $address
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_ids'   => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $user = $request->user();
        $cart = $user->cart;

        abort_if(! $cart, 422, 'Keranjang tidak ditemukan.');

        $cartItems = CartItem::where('cart_id', $cart->id)
            ->whereIn('product_id', $validated['product_ids'])
            ->with('product')
            ->get();

        abort_if($cartItems->isEmpty(), 422, 'Tidak ada produk yang dipilih.');

        // Validate stock for each item
        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                return back()->withErrors([
                    'stock' => "Stok produk \"{$item->product->name}\" tidak mencukupi.",
                ]);
            }
        }

        $totalPrice = $cartItems->sum(fn($item) => $item->price_snapshot * $item->quantity);

        DB::transaction(function () use ($user, $cartItems, $totalPrice) {
            $order = Order::create([
                'user_id'          => $user->id,
                'total_price'      => $totalPrice,
                'status'           => 'pending',
                'shipping_address' => null,
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $item->product_id,
                    'quantity'       => $item->quantity,
                    'price_snapshot' => $item->price_snapshot,
                    'subtotal'       => $item->price_snapshot * $item->quantity,
                ]);

                $item->product->decrement('stock', $item->quantity);
            }

            CartItem::whereIn('id', $cartItems->pluck('id'))->delete();
        });

        return redirect()->route('orders.index')->with('success', 'Pesanan berhasil dibuat!');
    }
}

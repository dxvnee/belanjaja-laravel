<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Log;

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

        $addresses = Address::where('user_id', $user->id)
            ->get()
            ->toArray();

        return Inertia::render('Checkout', [
            'title' => 'Checkout',
            'products' => $products,
            'addresses' => $addresses,
            'isBuyNow' => false,
        ]);
    }

    public function buyNow(Request $request)
    {
        $user = $request->user();
        $product_id = $request->input('product_id');
        $quantity = $request->input('quantity', 1);

        $product = Product::with('images')->findOrFail($product_id);

        $products = [
            [
                'id' => null,
                'product_id' => $product->id,
                'quantity' => (int) $quantity,
                'price_snapshot' => $product->price,
                'product' => $product,
            ]
        ];

        $addresses = Address::where('user_id', $user->id)
            ->get()
            ->toArray();

        return Inertia::render('Checkout', [
            'title' => 'Checkout',
            'products' => $products,
            'addresses' => $addresses,
            'isBuyNow' => true,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_ids'   => ['required', 'array', 'min:1'],
            'address'       => ['required', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'quantities'    => ['nullable', 'array'],
            'is_buy_now'    => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $productIds = $validated['product_ids'];
        $quantities = $request->input('quantities', []);

        $items = collect();

        if ($request->input('is_buy_now')) {
            $products = Product::whereIn('id', $productIds)->get();
            foreach ($products as $product) {
                $qty = (int) ($quantities[$product->id] ?? 1);
                $items->push((object)[
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price_snapshot' => $product->price,
                    'product' => $product,
                    'cart_item' => null,
                ]);
            }
        } else {
            $cart = $user->cart;
            abort_if(! $cart, 422, 'Keranjang tidak ditemukan.');

            $cartItems = CartItem::where('cart_id', $cart->id)
                ->whereIn('product_id', $productIds)
                ->with('product')
                ->get();

            foreach ($cartItems as $cartItem) {
                $items->push((object)[
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price_snapshot' => $cartItem->price_snapshot,
                    'product' => $cartItem->product,
                    'cart_item' => $cartItem,
                ]);
            }
        }

        abort_if($items->isEmpty(), 422, 'Tidak ada produk yang dipilih.');

        foreach ($items as $item) {
            if ($item->product->stock < $item->quantity) {
                return back()->withErrors([
                    'stock' => "Stok produk \"{$item->product->name}\" tidak mencukupi.",
                ]);
            }
        }

        $totalPrice = $items->sum(fn($item) => $item->price_snapshot * $item->quantity);

        DB::transaction(function () use ($user, $items, $totalPrice, $validated) {
            $order = Order::create([
                'user_id'          => $user->id,
                'total_price'      => $totalPrice,
                'status'           => 'pending',
                'shipping_address' => $validated['address'],
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $item->product_id,
                    'quantity'       => $item->quantity,
                    'price_snapshot' => $item->price_snapshot,
                    'subtotal'       => $item->price_snapshot * $item->quantity,
                ]);

                $item->product->decrement('stock', $item->quantity);

                if ($item->cart_item) {
                    $item->cart_item->delete();
                }
            }
        });

        return redirect()->route('orders.index')->with('success', 'Pesanan berhasil dibuat!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\ShippingService;
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

        $sellerLocation = $products->first()?->product?->location ?? 'Kota Jakarta Selatan';
        $defaultAddress = collect($addresses)->firstWhere('is_default', true) ?? ($addresses[0] ?? null);
        $buyerLocation = $defaultAddress['city'] ?? null;
        $shippingOptions = ShippingService::getOptions($sellerLocation, $buyerLocation);

        return Inertia::render('Checkout', [
            'title'           => 'Checkout',
            'products'        => $products,
            'addresses'       => $addresses,
            'isBuyNow'        => false,
            'shippingOptions' => $shippingOptions,
            'sellerLocation'  => $sellerLocation,
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

        $sellerLocation = $product->location ?? 'Kota Jakarta Selatan';
        $defaultAddress = collect($addresses)->firstWhere('is_default', true) ?? ($addresses[0] ?? null);
        $buyerLocation = $defaultAddress['city'] ?? null;
        $shippingOptions = ShippingService::getOptions($sellerLocation, $buyerLocation);

        return Inertia::render('Checkout', [
            'title'           => 'Checkout',
            'products'        => $products,
            'addresses'       => $addresses,
            'isBuyNow'        => true,
            'shippingOptions' => $shippingOptions,
            'sellerLocation'  => $sellerLocation,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_ids'      => ['required', 'array', 'min:1'],
            'address'          => ['required', 'array'],
            'product_ids.*'    => ['integer', 'exists:products,id'],
            'quantities'       => ['nullable', 'array'],
            'is_buy_now'       => ['nullable', 'boolean'],
            'shipping_service' => ['nullable', 'string', 'in:hemat,reguler,express'],
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

        $shippingService = $validated['shipping_service'] ?? ShippingService::SERVICE_REGULER;
        $buyerLocation = $validated['address']['city'] ?? null;
        $sellerLocation = $items->first()?->product?->location ?? null;
        $shippingCost = ShippingService::calculateCost($sellerLocation, $buyerLocation, $shippingService);

        $itemsSubtotal = $items->sum(fn($item) => $item->price_snapshot * $item->quantity);
        $totalPrice = $itemsSubtotal + $shippingCost;

        $order = DB::transaction(function () use ($user, $items, $totalPrice, $validated, $shippingService, $shippingCost) {
            $order = Order::create([
                'user_id'          => $user->id,
                'total_price'      => $totalPrice,
                'status'           => 'pending',
                'shipping_address' => $validated['address'],
                'shipping_service' => $shippingService,
                'shipping_cost'    => $shippingCost,
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

            return $order;
        });

        return redirect()->route('orders.payment', ['id' => $order->id])->with('success', 'Pesanan berhasil dibuat! Silakan selesaikan pembayaran.');
    }
}

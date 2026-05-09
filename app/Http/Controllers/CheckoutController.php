<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $product_ids = $request->input('product_ids', []);

        $cart = $request->user()->cart;

        $products = $cart
            ? CartItem::where('cart_id', $cart->id)
                ->whereIn('product_id', $product_ids)
                ->with('product.images')
                ->get()
            : collect();

        return Inertia::render('Checkout', [
            'title' => 'Checkout',
            'products' => $products,
        ]);
    }
}

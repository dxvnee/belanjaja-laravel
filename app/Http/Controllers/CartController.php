<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\In;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $cart = $this->checkCart($user);

        $cart_items = CartItem::where('cart_id', $cart->id)->get();

        $products = $cart_items->map(function ($item) {
            return Product::find($item->product_id)
                ->with('images')
                ->first();
        });

        return Inertia::render('Cart', [
            'products' => $products,
        ]);
    }

    public function add($id)
    {
        $user = Auth::user();
        $cart = $this->checkCart($user);

        $product = Product::findOrFail($id);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $id,
            'quantity' => 1,
            'price_snapshot' => $product->price,
        ]);

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    public function checkCart($user)
    {
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            $cart = Cart::firstOrCreate([
                'user_id' => $user->id
            ]);
        }
        return $cart;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $cart = $this->checkCart($user);

        $cart_items = CartItem::where('cart_id', $cart->id)
            ->with('product.images')
            ->get();

        return Inertia::render('Cart', [
            'cart_items' => $cart_items,
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $id = $request->input('product_id');
        $quantity = $request->input('quantity');

        $user = Auth::user();
        $cart = $this->checkCart($user);

        $product = Product::findOrFail($id);

        $isCarted = CartItem::where('cart_id', $cart->id)->where('product_id', $id)->first();

        if ($isCarted) {
            $isCarted->quantity += $quantity;
            if ($isCarted->quantity > $product->stock) {
                return back()->with('error', 'Jumlah melebihi stok yang tersedia!');
            }
            $isCarted->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $id,
                'quantity' => $quantity,
                'price_snapshot' => $product->price,
            ]);

            return back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
        }
    }

    public function remove($id)
    {
        try {
            $user = Auth::user();
            $cart = $this->checkCart($user);

            CartItem::where('cart_id', $cart->id)->where('product_id', $id)->delete();

            return back()->with('success', 'Produk berhasil dihapus dari keranjang!');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menghapus produk dari keranjang!');
        }
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

    public function updateQty(Request $request)
    {
        $user = Auth::user();
        $cart = $this->checkCart($user);

        $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ])['quantity'];

        CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->route('id'))
            ->update(['quantity' => $request->input('quantity')]);

        return back()->with('success', 'Jumlah produk berhasil diperbarui!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id'   => ['required', 'exists:orders,id'],
            'product_id' => ['required', 'exists:products,id'],
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'comment'    => ['nullable', 'string', 'max:1000'],
        ]);

        $order = $request->user()->orders()->findOrFail($validated['order_id']);

        if ($order->status !== 'completed') {
            return redirect()->back()->with('error', 'Hanya pesanan yang sudah selesai yang dapat diulas.');
        }

        $orderHasProduct = $order->items()->where('product_id', $validated['product_id'])->exists();
        if (!$orderHasProduct) {
            return redirect()->back()->with('error', 'Produk tidak ditemukan dalam pesanan ini.');
        }

        $alreadyReviewed = Review::where('order_id', $order->id)
            ->where('product_id', $validated['product_id'])
            ->exists();

        if ($alreadyReviewed) {
            return redirect()->back()->with('error', 'Anda sudah memberikan ulasan untuk produk ini pada pesanan tersebut.');
        }

        Review::create([
            'user_id'    => $request->user()->id,
            'order_id'   => $order->id,
            'product_id' => $validated['product_id'],
            'rating'     => $validated['rating'],
            'comment'    => $validated['comment'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Ulasan berhasil dikirim! Terima kasih atas masukan Anda.');
    }
}

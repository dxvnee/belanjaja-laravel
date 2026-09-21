<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with('items.product')
            ->latest()
            ->get();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function payment(Request $request, $id)
    {
        $order = $request->user()
            ->orders()
            ->with('items.product')
            ->findOrFail($id);

        if ($order->status !== 'pending') {
            return redirect()->route('orders.index')->with('error', 'Pesanan ini tidak dapat dibayar.');
        }

        return Inertia::render('Orders/Payment', [
            'order' => $order,
        ]);
    }

    public function pay(Request $request, $id)
    {
        $order = $request->user()
            ->orders()
            ->findOrFail($id);

        if ($order->status !== 'pending') {
            return redirect()->route('orders.index')->with('error', 'Pesanan ini tidak dapat dibayar.');
        }

        $request->validate([
            'payment_method' => ['required', 'string'],
        ]);

        $order->update([
            'status' => 'paid',
        ]);

        return redirect()->route('orders.index')->with('success', 'Pembayaran berhasil dilakukan!');
    }
}

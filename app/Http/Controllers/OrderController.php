<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Midtrans\Config;
use Midtrans\Snap;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with('items.product.images')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function payment(Request $request, $id)
    {
        $order = $request->user()
            ->orders()
            ->with(['items.product.images', 'user'])
            ->findOrFail($id);

        if ($order->status !== 'pending') {
            return redirect()->route('orders.index')->with('error', 'Pesanan ini sudah dibayar atau tidak dapat diproses.');
        }

        $snapToken = $order->snap_token;

        if (!$snapToken) {
            Config::$serverKey = config('services.midtrans.server_key');
            Config::$isProduction = (bool) config('services.midtrans.is_production', false);
            Config::$isSanitized = true;
            Config::$is3ds = true;

            $address = is_array($order->shipping_address) ? $order->shipping_address : [];

            $itemDetails = $order->items->map(function ($item) {
                return [
                    'id'       => (string) $item->product_id,
                    'price'    => (int) round($item->price_snapshot),
                    'quantity' => (int) $item->quantity,
                    'name'     => mb_substr($item->product->name ?? 'Produk', 0, 50),
                ];
            })->toArray();

            $params = [
                'transaction_details' => [
                    'order_id'     => 'ORDER-' . $order->id . '-' . time(),
                    'gross_amount' => (int) round($order->total_price),
                ],
                'customer_details' => [
                    'first_name' => $order->user->name,
                    'email'      => $order->user->email,
                    'phone'      => $address['phone'] ?? '',
                    'shipping_address' => [
                        'first_name'   => $address['name'] ?? $order->user->name,
                        'phone'        => $address['phone'] ?? '',
                        'address'      => $address['detail'] ?? '',
                        'city'         => $address['city'] ?? '',
                        'postal_code'  => $address['postal_code'] ?? '',
                        'country_code' => 'IDN',
                    ],
                ],
                'item_details' => $itemDetails,
            ];

            try {
                $snapToken = Snap::getSnapToken($params);
                $order->update(['snap_token' => $snapToken]);
            } catch (Exception $e) {
                Log::error('Midtrans Snap Error: ' . $e->getMessage());
            }
        }

        return Inertia::render('Orders/Payment', [
            'order'             => $order,
            'snapToken'         => $snapToken,
            'midtransClientKey' => config('services.midtrans.client_key'),
            'isProduction'      => (bool) config('services.midtrans.is_production', false),
        ]);
    }

    public function pay(Request $request, $id)
    {
        $order = $request->user()
            ->orders()
            ->findOrFail($id);

        if ($order->status !== 'pending') {
            return redirect()->route('orders.index')->with('error', 'Pesanan ini sudah dibayar atau tidak dapat diproses.');
        }

        $validated = $request->validate([
            'payment_method'     => ['nullable', 'string'],
            'transaction_status' => ['nullable', 'string'],
        ]);

        $order->update([
            'status'       => 'paid',
            'payment_type' => $validated['payment_method'] ?? 'midtrans',
        ]);

        return redirect()->route('orders.index')->with('success', 'Pembayaran berhasil diverifikasi!');
    }

    public function cancel(Request $request, $id)
    {
        $order = $request->user()->orders()->findOrFail($id);

        if ($order->status !== 'pending') {
            return redirect()->route('orders.index')->with('error', 'Pesanan ini tidak dapat dibatalkan.');
        }

        $order->update(['status' => 'cancelled']);

        foreach ($order->items as $item) {
            $item->product?->increment('stock', $item->quantity);
        }

        return redirect()->route('orders.index')->with('success', 'Pesanan berhasil dibatalkan.');
    }

    public function complete(Request $request, $id)
    {
        $order = $request->user()->orders()->findOrFail($id);

        if ($order->status !== 'shipped') {
            return redirect()->route('orders.index')->with('error', 'Hanya pesanan yang sedang dikirim yang dapat dikonfirmasi.');
        }

        $order->update(['status' => 'completed']);

        return redirect()->route('orders.index')->with('success', 'Pesanan telah selesai! Terima kasih telah berbelanja.');
    }

    public function callback(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');
        $signatureKey = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($signatureKey !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature key'], 403);
        }

        // Format order_id: ORDER-{id}-{timestamp}
        $parts = explode('-', $request->order_id);
        $orderId = isset($parts[1]) ? (int) $parts[1] : (int) $request->order_id;

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $transaction = $request->transaction_status;
        $type = $request->payment_type;
        $fraud = $request->fraud_status;

        if ($transaction === 'capture') {
            if ($fraud === 'accept') {
                $order->update(['status' => 'paid', 'payment_type' => $type]);
            }
        } elseif ($transaction === 'settlement') {
            $order->update(['status' => 'paid', 'payment_type' => $type]);
        } elseif ($transaction === 'pending') {
            $order->update(['status' => 'pending', 'payment_type' => $type]);
        } elseif (in_array($transaction, ['deny', 'expire', 'cancel'])) {
            if ($order->status !== 'cancelled') {
                $order->update(['status' => 'cancelled']);
                foreach ($order->items as $item) {
                    $item->product?->increment('stock', $item->quantity);
                }
            }
        }

        return response()->json(['message' => 'Notification handled successfully']);
    }
}

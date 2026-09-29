<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookService
{
    /**
     * Handle incoming Midtrans notification webhook.
     *
     * @param Request|array $payload
     * @return array ['success' => bool, 'status' => int, 'message' => string]
     */
    public static function handle($payload): array
    {
        $data = $payload instanceof Request ? $payload->all() : $payload;

        $orderIdRaw = (string) ($data['order_id'] ?? '');
        $statusCode = (string) ($data['status_code'] ?? '');
        $grossAmount = (string) ($data['gross_amount'] ?? '');
        $signatureKey = (string) ($data['signature_key'] ?? '');
        $transactionStatus = (string) ($data['transaction_status'] ?? '');
        $paymentType = (string) ($data['payment_type'] ?? 'midtrans');
        $fraudStatus = (string) ($data['fraud_status'] ?? '');

        $serverKey = config('services.midtrans.server_key');

        if (empty($serverKey)) {
            Log::error('Midtrans Webhook: Server key is not configured.');
            return ['success' => false, 'status' => 500, 'message' => 'Midtrans server key not configured'];
        }

        // 1. Verify cryptographic SHA512 signature using constant-time comparison to prevent timing attacks
        $expectedSignature = hash('sha512', $orderIdRaw . $statusCode . $grossAmount . $serverKey);

        if (! hash_equals($expectedSignature, $signatureKey)) {
            Log::warning("Midtrans Webhook: Invalid signature for Order {$orderIdRaw}.");
            return ['success' => false, 'status' => 403, 'message' => 'Invalid signature key'];
        }

        // 2. Parse internal order ID from format "ORDER-{id}-{timestamp}" or standard "{id}"
        $orderId = static::extractOrderId($orderIdRaw);
        $order = Order::with('items.product')->find($orderId);

        if (! $order) {
            Log::warning("Midtrans Webhook: Order not found for ID {$orderIdRaw}.");
            return ['success' => false, 'status' => 404, 'message' => 'Order not found'];
        }

        // 3. Process transaction status with idempotency and atomic stock restoration
        return DB::transaction(function () use ($order, $transactionStatus, $paymentType, $fraudStatus, $orderIdRaw) {
            switch ($transactionStatus) {
                case 'capture':
                    if ($fraudStatus === 'challenge') {
                        $order->update([
                            'payment_type' => $paymentType,
                        ]);
                        Log::info("Midtrans Webhook: Order {$orderIdRaw} challenged by fraud detection.");
                    } elseif ($fraudStatus === 'accept') {
                        $order->update([
                            'status'       => 'paid',
                            'payment_type' => $paymentType,
                        ]);
                        Log::info("Midtrans Webhook: Order {$orderIdRaw} successfully captured and paid.");
                    }
                    break;

                case 'settlement':
                    // Idempotency: don't overwrite if already processed further
                    if (! in_array($order->status, ['paid', 'shipped', 'completed'])) {
                        $order->update([
                            'status'       => 'paid',
                            'payment_type' => $paymentType,
                        ]);
                        Log::info("Midtrans Webhook: Order {$orderIdRaw} settled and marked as paid.");
                    }
                    break;

                case 'pending':
                    if ($order->status === 'pending') {
                        $order->update(['payment_type' => $paymentType]);
                    }
                    break;

                case 'deny':
                case 'expire':
                case 'cancel':
                    // Idempotent cancellation: only cancel and restore stock once
                    if ($order->status !== 'cancelled') {
                        $order->update(['status' => 'cancelled']);
                        foreach ($order->items as $item) {
                            $item->product?->increment('stock', $item->quantity);
                        }
                        Log::info("Midtrans Webhook: Order {$orderIdRaw} {$transactionStatus}, stock restored.");
                    }
                    break;

                default:
                    Log::info("Midtrans Webhook: Unhandled status {$transactionStatus} for Order {$orderIdRaw}.");
                    break;
            }

            return ['success' => true, 'status' => 200, 'message' => 'Notification handled successfully'];
        });
    }

    /**
     * Extract database order ID from various midtrans order_id formats.
     */
    public static function extractOrderId(string $orderIdRaw): int
    {
        if (str_starts_with($orderIdRaw, 'ORDER-')) {
            $parts = explode('-', $orderIdRaw);
            return isset($parts[1]) ? (int) $parts[1] : (int) $orderIdRaw;
        }

        return (int) $orderIdRaw;
    }
}

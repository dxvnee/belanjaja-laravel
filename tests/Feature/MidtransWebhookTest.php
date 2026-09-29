<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MidtransWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $serverKey = 'SB-Mid-server-testkey12345';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.midtrans.server_key' => $this->serverKey]);
    }

    private function createOrderWithStock(int $initialStock = 5, int $quantity = 2): array
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Elektronik', 'slug' => 'elektronik']);

        $product = Product::create([
            'user_id'     => $user->id,
            'category_id' => $category->id,
            'name'        => 'Keyboard Gaming RGB',
            'slug'        => 'keyboard-gaming-rgb',
            'description' => 'Keyboard mechanical',
            'price'       => 300000,
            'stock'       => $initialStock,
            'is_active'   => true,
            'location'    => 'Jakarta',
        ]);

        $order = Order::create([
            'user_id'          => $user->id,
            'total_price'      => 600000,
            'status'           => 'pending',
            'shipping_service' => 'reguler',
            'shipping_cost'    => 10000,
            'shipping_address' => ['name' => 'John', 'city' => 'Jakarta'],
        ]);

        $orderItem = OrderItem::create([
            'order_id'       => $order->id,
            'product_id'     => $product->id,
            'quantity'       => $quantity,
            'price_snapshot' => 300000,
            'subtotal'       => 600000,
        ]);

        return [$order, $product, $orderItem];
    }

    public function test_webhook_with_valid_signature_updates_order_to_paid(): void
    {
        [$order, $product] = $this->createOrderWithStock();

        $orderIdRaw = "ORDER-{$order->id}-" . time();
        $statusCode = '200';
        $grossAmount = '600000.00';
        $signature = hash('sha512', $orderIdRaw . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id'           => $orderIdRaw,
            'status_code'        => $statusCode,
            'gross_amount'       => $grossAmount,
            'signature_key'      => $signature,
            'transaction_status' => 'settlement',
            'payment_type'       => 'gopay',
        ];

        $response = $this->postJson(route('midtrans.webhook'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Notification handled successfully']);

        $order->refresh();
        $this->assertEquals('paid', $order->status);
        $this->assertEquals('gopay', $order->payment_type);
    }

    public function test_webhook_with_invalid_signature_returns_403_and_leaves_order_pending(): void
    {
        [$order, $product] = $this->createOrderWithStock();

        $orderIdRaw = "ORDER-{$order->id}-" . time();

        $payload = [
            'order_id'           => $orderIdRaw,
            'status_code'        => '200',
            'gross_amount'       => '600000.00',
            'signature_key'      => 'invalid-fake-signature-key-1234567890',
            'transaction_status' => 'settlement',
            'payment_type'       => 'bank_transfer',
        ];

        $response = $this->postJson(route('midtrans.webhook'), $payload);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Invalid signature key']);

        $order->refresh();
        $this->assertEquals('pending', $order->status);
    }

    public function test_webhook_handles_credit_card_capture_accept(): void
    {
        [$order, $product] = $this->createOrderWithStock();

        $orderIdRaw = "ORDER-{$order->id}-" . time();
        $statusCode = '200';
        $grossAmount = '600000.00';
        $signature = hash('sha512', $orderIdRaw . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id'           => $orderIdRaw,
            'status_code'        => $statusCode,
            'gross_amount'       => $grossAmount,
            'signature_key'      => $signature,
            'transaction_status' => 'capture',
            'fraud_status'       => 'accept',
            'payment_type'       => 'credit_card',
        ];

        $response = $this->postJson(route('midtrans.callback'), $payload);

        $response->assertStatus(200);
        $order->refresh();
        $this->assertEquals('paid', $order->status);
        $this->assertEquals('credit_card', $order->payment_type);
    }

    public function test_webhook_cancels_order_and_atomically_restores_stock_on_expire(): void
    {
        [$order, $product] = $this->createOrderWithStock(initialStock: 5, quantity: 3);

        $orderIdRaw = "ORDER-{$order->id}-" . time();
        $statusCode = '202';
        $grossAmount = '600000.00';
        $signature = hash('sha512', $orderIdRaw . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id'           => $orderIdRaw,
            'status_code'        => $statusCode,
            'gross_amount'       => $grossAmount,
            'signature_key'      => $signature,
            'transaction_status' => 'expire',
            'payment_type'       => 'bank_transfer',
        ];

        $response = $this->postJson(route('midtrans.webhook'), $payload);

        $response->assertStatus(200);
        $order->refresh();
        $this->assertEquals('cancelled', $order->status);

        // Product stock should have restored (+3) from 5 to 8
        $this->assertEquals(8, $product->fresh()->stock);

        // Idempotency test: Re-sending expire notification should not increment stock again!
        $this->postJson(route('midtrans.webhook'), $payload);
        $this->assertEquals(8, $product->fresh()->stock);
    }

    public function test_webhook_handles_idempotency_without_overwriting_shipped_order(): void
    {
        [$order, $product] = $this->createOrderWithStock();
        $order->update(['status' => 'shipped', 'tracking_number' => 'JNE12345678']);

        $orderIdRaw = "ORDER-{$order->id}-" . time();
        $statusCode = '200';
        $grossAmount = '600000.00';
        $signature = hash('sha512', $orderIdRaw . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id'           => $orderIdRaw,
            'status_code'        => $statusCode,
            'gross_amount'       => $grossAmount,
            'signature_key'      => $signature,
            'transaction_status' => 'settlement',
            'payment_type'       => 'qris',
        ];

        $response = $this->postJson(route('midtrans.webhook'), $payload);

        $response->assertStatus(200);
        $order->refresh();
        // Should remain 'shipped', not reverted back to 'paid'
        $this->assertEquals('shipped', $order->status);
    }
}

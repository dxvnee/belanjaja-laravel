<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFulfillmentTest extends TestCase
{
    use RefreshDatabase;

    private function createData(): array
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        $product = Product::create([
            'user_id'     => $seller->id,
            'category_id' => $category->id,
            'name'        => 'Keyboard Mechanical',
            'slug'        => 'keyboard-mechanical',
            'description' => 'Keyboard mechanical enak dipakai.',
            'price'       => 500000,
            'stock'       => 8,
            'is_active'   => true,
        ]);

        return [$seller, $buyer, $product];
    }

    public function test_seller_can_view_incoming_orders(): void
    {
        [$seller, $buyer, $product] = $this->createData();

        $order = Order::create([
            'user_id'          => $buyer->id,
            'total_price'      => 500000,
            'status'           => 'paid',
            'shipping_address' => ['name' => 'Budi', 'city' => 'Jakarta'],
        ]);

        OrderItem::create([
            'order_id'       => $order->id,
            'product_id'     => $product->id,
            'quantity'       => 1,
            'price_snapshot' => 500000,
            'subtotal'       => 500000,
        ]);

        $response = $this->actingAs($seller)->get(route('admin.orders'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Orders/SellerOrders')
                ->has('orders.data', 1)
        );
    }

    public function test_seller_can_ship_order_with_tracking_number(): void
    {
        [$seller, $buyer, $product] = $this->createData();

        $order = Order::create([
            'user_id'          => $buyer->id,
            'total_price'      => 500000,
            'status'           => 'paid',
            'shipping_address' => ['name' => 'Budi'],
        ]);

        OrderItem::create([
            'order_id'       => $order->id,
            'product_id'     => $product->id,
            'quantity'       => 1,
            'price_snapshot' => 500000,
            'subtotal'       => 500000,
        ]);

        $response = $this->actingAs($seller)->post(route('admin.orders.ship', $order->id), [
            'tracking_number' => 'RESI12345678',
        ]);

        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('shipped', $order->status);
        $this->assertEquals('RESI12345678', $order->tracking_number);
    }

    public function test_buyer_can_confirm_order_completed(): void
    {
        [$seller, $buyer, $product] = $this->createData();

        $order = Order::create([
            'user_id'          => $buyer->id,
            'total_price'      => 500000,
            'status'           => 'shipped',
            'tracking_number'  => 'RESI12345678',
            'shipping_address' => ['name' => 'Budi'],
        ]);

        $response = $this->actingAs($buyer)->post(route('orders.complete', $order->id));

        $response->assertRedirect(route('orders.index'));

        $order->refresh();
        $this->assertEquals('completed', $order->status);
    }

    public function test_buyer_can_cancel_pending_order_and_stock_is_restored(): void
    {
        [$seller, $buyer, $product] = $this->createData();

        // Product stock is initially 8
        $order = Order::create([
            'user_id'          => $buyer->id,
            'total_price'      => 1000000,
            'status'           => 'pending',
            'shipping_address' => ['name' => 'Budi'],
        ]);

        OrderItem::create([
            'order_id'       => $order->id,
            'product_id'     => $product->id,
            'quantity'       => 2,
            'price_snapshot' => 500000,
            'subtotal'       => 1000000,
        ]);

        $response = $this->actingAs($buyer)->post(route('orders.cancel', $order->id));

        $response->assertRedirect(route('orders.index'));

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);

        // Stock restored from 8 to 10
        $product->refresh();
        $this->assertEquals(10, $product->stock);
    }
}

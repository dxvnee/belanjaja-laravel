<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_checkout_with_address(): void
    {
        $user = User::factory()->create();
        $seller = User::factory()->create();

        // Create a category
        $category = Category::create([
            'name' => 'Electronic',
            'slug' => 'electronic',
        ]);

        // Create a product
        $product = Product::factory()->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'stock' => 10,
            'price' => 100000,
        ]);

        // Create a cart for the user
        $cart = Cart::create([
            'user_id' => $user->id,
        ]);

        // Add item to cart
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price_snapshot' => 100000,
        ]);

        $addressData = [
            'name' => 'Eigiya Daramuli Kale',
            'phone' => '081366366550',
            'detail' => 'Jalan Tanimbar No. 16, RT.7/RW.5, Cimone Jaya',
            'subdistrict' => 'Cimone Jaya',
            'city' => 'KOTA TANGERANG',
            'province' => 'BANTEN',
            'postal_code' => '15114',
        ];

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'product_ids' => [$product->id],
            'address' => $addressData,
        ]);

        $response->assertRedirect(route('orders.index'));

        // Assert database has order
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_price' => 200000,
            'status' => 'pending',
        ]);

        // Check if shipping_address cast works
        $order = Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals($addressData, $order->shipping_address);
    }

    public function test_authenticated_user_can_buy_now_with_address(): void
    {
        $user = User::factory()->create();
        $seller = User::factory()->create();

        // Create a category
        $category = Category::create([
            'name' => 'Electronic',
            'slug' => 'electronic',
        ]);

        // Create a product
        $product = Product::factory()->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'stock' => 10,
            'price' => 120000,
        ]);

        $addressData = [
            'name' => 'Eigiya Daramuli Kale',
            'phone' => '081366366550',
            'detail' => 'Jalan Tanimbar No. 16, RT.7/RW.5, Cimone Jaya',
            'subdistrict' => 'Cimone Jaya',
            'city' => 'KOTA TANGERANG',
            'province' => 'BANTEN',
            'postal_code' => '15114',
        ];

        // 1. Test the buyNow page load returns the products correctly formatted
        $response = $this->actingAs($user)->get(route('checkout.buyNow', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]));

        $response->assertStatus(200);

        // 2. Test submitting the order via Buy Now
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'product_ids' => [$product->id],
            'quantities' => [
                $product->id => 3,
            ],
            'is_buy_now' => true,
            'address' => $addressData,
        ]);

        $response->assertRedirect(route('orders.index'));

        // Assert database has order
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_price' => 360000, // 3 * 120000
            'status' => 'pending',
        ]);

        // Assert product stock decremented correctly
        $this->assertEquals(7, $product->fresh()->stock);

        // Check if shipping_address cast works
        $order = Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals($addressData, $order->shipping_address);
    }
}

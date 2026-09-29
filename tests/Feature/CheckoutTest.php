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
            'location' => 'Bandung',
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

        $order = Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('orders.payment', ['id' => $order->id]));

        // Assert database has order with calculated shipping cost
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_price' => 222000,
            'shipping_service' => 'reguler',
            'shipping_cost' => 22000,
            'status' => 'pending',
        ]);

        // Check if shipping_address cast works
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
            'location' => 'Bandung',
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

        $order = Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('orders.payment', ['id' => $order->id]));

        // Assert database has order with calculated shipping cost
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_price' => 382000, // 3 * 120000 + 22000 shipping
            'shipping_service' => 'reguler',
            'shipping_cost' => 22000,
            'status' => 'pending',
        ]);

        // Assert product stock decremented correctly
        $this->assertEquals(7, $product->fresh()->stock);

        // Check if shipping_address cast works
        $this->assertEquals($addressData, $order->shipping_address);
    }

    public function test_checkout_fails_and_rolls_back_if_stock_is_insufficient(): void
    {
        $user = User::factory()->create();
        $seller = User::factory()->create();
        $category = Category::create(['name' => 'Buku', 'slug' => 'buku']);

        $product = Product::factory()->create([
            'user_id'     => $seller->id,
            'category_id' => $category->id,
            'stock'       => 2,
            'price'       => 50000,
            'location'    => 'Bandung',
        ]);

        $addressData = [
            'name'        => 'Ahmad',
            'phone'       => '08123456789',
            'detail'      => 'Jl. Merdeka No 1',
            'subdistrict' => 'Coblong',
            'city'        => 'Bandung',
            'province'    => 'Jawa Barat',
            'postal_code' => '40132',
        ];

        // Attempt to buy 5 when stock is only 2
        $response = $this->actingAs($user)->from(route('checkout.index'))->post(route('checkout.store'), [
            'product_ids' => [$product->id],
            'quantities'  => [$product->id => 5],
            'is_buy_now'  => true,
            'address'     => $addressData,
        ]);

        $response->assertSessionHasErrors('stock');
        $this->assertEquals(0, Order::count());
        $this->assertEquals(2, $product->fresh()->stock); // Stock intact, no partial decrement
    }
}


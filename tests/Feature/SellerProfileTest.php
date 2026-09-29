<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_seller_profile_page(): void
    {
        $seller = User::factory()->create([
            'name' => 'Toko Maju Jaya',
        ]);
        $viewer = User::factory()->create();

        $category = Category::create([
            'name' => 'Pakaian',
            'slug' => 'pakaian',
        ]);

        $activeProduct = Product::create([
            'user_id'     => $seller->id,
            'category_id' => $category->id,
            'name'        => 'Kemeja Flanel',
            'slug'        => 'kemeja-flanel',
            'description' => 'Kemeja flanel bahan premium.',
            'price'       => 150000,
            'stock'       => 20,
            'is_active'   => true,
        ]);

        $inactiveProduct = Product::create([
            'user_id'     => $seller->id,
            'category_id' => $category->id,
            'name'        => 'Kaos Polos Nonaktif',
            'slug'        => 'kaos-polos-nonaktif',
            'description' => 'Produk tidak aktif.',
            'price'       => 50000,
            'stock'       => 0,
            'is_active'   => false,
        ]);

        $response = $this->actingAs($viewer)->get(route('seller.show', $seller->id));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('SellerProfile')
                ->has('seller')
                ->where('seller.name', 'Toko Maju Jaya')
                ->where('seller.products_count', 1)
                ->has('products.data', 1)
                ->where('products.data.0.name', 'Kemeja Flanel')
        );
    }

    public function test_seller_profile_displays_average_rating(): void
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
            'name'        => 'Headphone Bluetooth',
            'slug'        => 'headphone-bluetooth',
            'description' => 'Headphone suara jernih.',
            'price'       => 300000,
            'stock'       => 10,
            'is_active'   => true,
        ]);

        $order = Order::create([
            'user_id'     => $buyer->id,
            'total_price' => 300000,
            'status'      => 'completed',
        ]);

        OrderItem::create([
            'order_id'       => $order->id,
            'product_id'     => $product->id,
            'quantity'       => 1,
            'price_snapshot' => 300000,
            'subtotal'       => 300000,
        ]);

        Review::create([
            'user_id'    => $buyer->id,
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'rating'     => 5,
            'comment'    => 'Sangat memuaskan!',
        ]);

        $response = $this->actingAs($buyer)->get(route('seller.show', $seller->id));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('SellerProfile')
                ->where('seller.avg_rating', fn($v) => (float) $v === 5.0)
                ->where('seller.reviews_count', 1)
        );
    }

    public function test_seller_profile_returns_404_for_invalid_id(): void
    {
        $viewer = User::factory()->create();

        $response = $this->actingAs($viewer)->get(route('seller.show', 999999));

        $response->assertStatus(404);
    }

    public function test_product_detail_page_includes_seller_with_products_count(): void
    {
        $seller = User::factory()->create(['name' => 'Juragan Gadget']);
        $viewer = User::factory()->create();

        $category = Category::create([
            'name' => 'Gadget',
            'slug' => 'gadget',
        ]);

        $product = Product::create([
            'user_id'     => $seller->id,
            'category_id' => $category->id,
            'name'        => 'Smartphone 5G',
            'slug'        => 'smartphone-5g',
            'description' => 'Smartphone performa kencang.',
            'price'       => 4000000,
            'stock'       => 5,
            'is_active'   => true,
        ]);

        $response = $this->actingAs($viewer)->get(route('product.show', $product->id));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('ProductDetail')
                ->has('product.user')
                ->where('product.user.name', 'Juragan Gadget')
                ->where('product.user.products_count', 1)
        );
    }
}

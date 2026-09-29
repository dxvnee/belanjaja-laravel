<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
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
            'name'        => 'Mouse Gaming RGB',
            'slug'        => 'mouse-gaming-rgb',
            'description' => 'Mouse gaming dengan sensor presisi.',
            'price'       => 250000,
            'stock'       => 15,
            'is_active'   => true,
        ]);

        return [$seller, $buyer, $product];
    }

    public function test_buyer_can_review_product_from_completed_order(): void
    {
        [$seller, $buyer, $product] = $this->createData();

        $order = Order::create([
            'user_id'     => $buyer->id,
            'total_price' => 250000,
            'status'      => 'completed',
        ]);

        OrderItem::create([
            'order_id'       => $order->id,
            'product_id'     => $product->id,
            'quantity'       => 1,
            'price_snapshot' => 250000,
            'subtotal'       => 250000,
        ]);

        $response = $this->actingAs($buyer)->post(route('reviews.store'), [
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'rating'     => 5,
            'comment'    => 'Barang mantap, pengiriman cepat!',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'user_id'    => $buyer->id,
            'rating'     => 5,
            'comment'    => 'Barang mantap, pengiriman cepat!',
        ]);
    }

    public function test_buyer_cannot_review_order_not_completed(): void
    {
        [$seller, $buyer, $product] = $this->createData();

        $order = Order::create([
            'user_id'     => $buyer->id,
            'total_price' => 250000,
            'status'      => 'shipped',
        ]);

        OrderItem::create([
            'order_id'       => $order->id,
            'product_id'     => $product->id,
            'quantity'       => 1,
            'price_snapshot' => 250000,
            'subtotal'       => 250000,
        ]);

        $response = $this->actingAs($buyer)->post(route('reviews.store'), [
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'rating'     => 4,
            'comment'    => 'Barang belum sampai tapi mau review',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_buyer_cannot_review_product_not_in_order(): void
    {
        [$seller, $buyer, $product] = $this->createData();

        $category = Category::first();
        $otherProduct = Product::create([
            'user_id'     => $seller->id,
            'category_id' => $category->id,
            'name'        => 'Headset Gaming',
            'slug'        => 'headset-gaming',
            'description' => 'Headset dengan surround sound.',
            'price'       => 350000,
            'stock'       => 5,
            'is_active'   => true,
        ]);

        $order = Order::create([
            'user_id'     => $buyer->id,
            'total_price' => 250000,
            'status'      => 'completed',
        ]);

        OrderItem::create([
            'order_id'       => $order->id,
            'product_id'     => $product->id,
            'quantity'       => 1,
            'price_snapshot' => 250000,
            'subtotal'       => 250000,
        ]);

        $response = $this->actingAs($buyer)->post(route('reviews.store'), [
            'order_id'   => $order->id,
            'product_id' => $otherProduct->id,
            'rating'     => 5,
            'comment'    => 'Reviewing item not bought in this order',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_buyer_cannot_review_same_product_in_same_order_twice(): void
    {
        [$seller, $buyer, $product] = $this->createData();

        $order = Order::create([
            'user_id'     => $buyer->id,
            'total_price' => 250000,
            'status'      => 'completed',
        ]);

        OrderItem::create([
            'order_id'       => $order->id,
            'product_id'     => $product->id,
            'quantity'       => 1,
            'price_snapshot' => 250000,
            'subtotal'       => 250000,
        ]);

        Review::create([
            'user_id'    => $buyer->id,
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'rating'     => 5,
            'comment'    => 'Pertama kali review',
        ]);

        $response = $this->actingAs($buyer)->post(route('reviews.store'), [
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'rating'     => 4,
            'comment'    => 'Coba review lagi',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_product_detail_page_loads_reviews_and_avg_rating(): void
    {
        [$seller, $buyer, $product] = $this->createData();

        $order = Order::create([
            'user_id'     => $buyer->id,
            'total_price' => 250000,
            'status'      => 'completed',
        ]);

        Review::create([
            'user_id'    => $buyer->id,
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'rating'     => 5,
            'comment'    => 'Kualitas jempolan!',
        ]);

        $response = $this->actingAs($buyer)->get(route('product.show', $product->id));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('ProductDetail')
                ->has('product.reviews', 1)
                ->where('product.reviews_count', 1)
                ->where('product.reviews_avg_rating', fn($val) => (float) $val === 5.0)
        );
    }
}

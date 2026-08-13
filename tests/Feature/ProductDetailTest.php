<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_detail_page_displays_correctly(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'name' => 'iPhone 13 Pro Max',
            'slug' => 'iphone-13-pro-max',
            'description' => 'iPhone 13 Pro Max kondisi mulus',
            'price' => 15000000,
            'stock' => 5,
            'is_active' => true,
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/iphone.jpg',
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/iphone2.jpg',
        ]);

        $response = $this->actingAs($user)->get(route('product.show', ['id' => $product->id]));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('ProductDetail')
                ->has('product')
                ->where('product.name', 'iPhone 13 Pro Max')
                ->where('product.slug', 'iphone-13-pro-max')
                ->where('product.price', 15000000)
                ->where('product.stock', 5)
                ->has('product.images', 2)
        );
    }

    public function test_product_detail_page_returns_404_for_invalid_slug(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('product.show', ['id' => 9999]));

        $response->assertStatus(404);
    }

    public function test_product_detail_requires_authentication(): void
    {
        $product = Product::factory()->create(['slug' => 'test-product']);

        $response = $this->get(route('product.show', ['id' => $product->id]));

        $response->assertRedirect(route('login'));
    }
}

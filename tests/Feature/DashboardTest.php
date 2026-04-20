<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_all_active_products(): void
    {
        $user = User::factory()->create();

        // Create active products with images
        $activeProduct1 = Product::factory()->create([
            'name' => 'iPhone 13',
            'is_active' => true,
        ]);
        ProductImage::create([
            'product_id' => $activeProduct1->id,
            'image_path' => 'products/iphone.jpg',
        ]);

        $activeProduct2 = Product::factory()->create([
            'name' => 'Samsung Galaxy',
            'is_active' => true,
        ]);
        ProductImage::create([
            'product_id' => $activeProduct2->id,
            'image_path' => 'products/samsung.jpg',
        ]);

        // Create inactive product (should not be displayed)
        Product::factory()->create([
            'name' => 'Inactive Product',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Dashboard')
                ->has('products', 2)
                ->has('products.0.images', 1)
                ->has('products.1.images', 1)
        );
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }
}

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
                ->has('products.data', 2)
                ->has('products.data.0.images', 1)
                ->has('products.data.1.images', 1)
        );
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_search_products_via_query_string(): void
    {
        $user = User::factory()->create();

        // Product matching by name
        Product::factory()->create([
            'name' => 'Laptop Asus ROG Zephyrus',
            'description' => 'Komputer portabel gaming cepat',
            'is_active' => true,
        ]);

        // Product matching by description
        Product::factory()->create([
            'name' => 'Mouse Wireless Ergonomis',
            'description' => 'Aksesoris gaming nyaman untuk produktivitas',
            'is_active' => true,
        ]);

        // Non-matching product
        Product::factory()->create([
            'name' => 'Meja Belajar Kayu',
            'description' => 'Perabotan rumah minimalis',
            'is_active' => true,
        ]);

        // Inactive product matching keyword (should NOT be returned)
        Product::factory()->create([
            'name' => 'Monitor Gaming 144Hz',
            'description' => 'Layar gaming rusak',
            'is_active' => false,
        ]);

        // 1. Search by name keyword via GET query string
        $response1 = $this->actingAs($user)->get(route('dashboard.search', ['query' => 'asus']));
        $response1->assertStatus(200);
        $response1->assertInertia(
            fn($page) => $page
                ->component('Search')
                ->has('products.data', 1)
                ->where('products.data.0.name', 'Laptop Asus ROG Zephyrus')
                ->where('query', 'asus')
        );

        // 2. Search by description keyword via GET query string (case-insensitive)
        $response2 = $this->actingAs($user)->get(route('dashboard.search', ['query' => 'GAMING']));
        $response2->assertStatus(200);
        $response2->assertInertia(
            fn($page) => $page
                ->component('Search')
                ->has('products.data', 2)
                ->where('query', 'GAMING')
        );

        // 3. Search via POST also works
        $response3 = $this->actingAs($user)->post(route('dashboard.search'), ['query' => 'asus']);
        $response3->assertStatus(200);
        $response3->assertInertia(
            fn($page) => $page
                ->component('Search')
                ->has('products.data', 1)
        );
    }

    public function test_dashboard_can_filter_products_by_category(): void
    {
        $user = User::factory()->create();

        $catElectronics = \App\Models\Category::firstOrCreate(
            ['slug' => 'elektronik'],
            ['name' => 'Elektronik', 'slug' => 'elektronik']
        );
        $catFashion = \App\Models\Category::firstOrCreate(
            ['slug' => 'fashion'],
            ['name' => 'Fashion', 'slug' => 'fashion']
        );

        Product::factory()->create([
            'name' => 'Laptop Dell',
            'category_id' => $catElectronics->id,
            'is_active' => true,
        ]);
        Product::factory()->create([
            'name' => 'Kemeja Pria',
            'category_id' => $catFashion->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', ['category' => 'elektronik']));
        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Dashboard')
                ->has('products.data', 1)
                ->where('products.data.0.name', 'Laptop Dell')
                ->where('selectedCategory', 'elektronik')
                ->has('categories')
                ->has('totalProductsCount')
        );
    }

    public function test_user_can_get_instant_search_preview(): void
    {
        $user = User::factory()->create();

        Product::factory()->create([
            'name' => 'Sony Headphone WH-1000XM5',
            'price' => 4500000,
            'stock' => 10,
            'is_active' => true,
        ]);
        Product::factory()->create([
            'name' => 'Sony Speaker Bluetooth',
            'price' => 1200000,
            'stock' => 5,
            'is_active' => true,
        ]);
        Product::factory()->create([
            'name' => 'Bose Headphone',
            'price' => 3800000,
            'stock' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->getJson(route('dashboard.search.preview', ['query' => 'Sony']));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'products' => [
                '*' => ['id', 'name', 'price', 'stock', 'images', 'category'],
            ],
        ]);
        $response->assertJsonCount(2, 'products');
    }

    public function test_search_preview_returns_empty_when_query_is_empty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('dashboard.search.preview', ['query' => '']));
        $response->assertStatus(200);
        $response->assertExactJson(['products' => []]);
    }

    public function test_search_preview_does_not_return_inactive_products(): void
    {
        $user = User::factory()->create();

        Product::factory()->create([
            'name' => 'Sony Inactive Gadget',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->getJson(route('dashboard.search.preview', ['query' => 'Sony']));
        $response->assertStatus(200);
        $response->assertExactJson(['products' => []]);
    }
}

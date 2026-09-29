<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\ShippingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewEcommerceFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_have_up_to_3_categories(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $cat1 = Category::create(['name' => 'Elektronik', 'slug' => 'elektronik']);
        $cat2 = Category::create(['name' => 'Gadget', 'slug' => 'gadget']);
        $cat3 = Category::create(['name' => 'Aksesoris', 'slug' => 'aksesoris']);

        $response = $this->actingAs($user)->post(route('jual.store'), [
            'judul'        => 'Mouse Gaming RGB',
            'deskripsi'    => 'Mouse gaming presisi tinggi dengan RGB 7 warna',
            'harga'        => 250000,
            'stok'         => 10,
            'kategori_ids' => [$cat1->id, $cat2->id, $cat3->id],
            'location'     => 'Jakarta Barat',
            'photo1'       => UploadedFile::fake()->image('mouse.jpg'),
        ]);

        $response->assertRedirect(route('dashboard'));

        $product = Product::where('name', 'Mouse Gaming RGB')->first();
        $this->assertNotNull($product);
        $this->assertEquals($cat1->id, $product->category_id); // Primary category
        $this->assertCount(3, $product->categories);
        $this->assertTrue($product->categories->contains($cat1));
        $this->assertTrue($product->categories->contains($cat2));
        $this->assertTrue($product->categories->contains($cat3));
    }

    public function test_product_cannot_have_more_than_3_categories(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $cats = [];
        for ($i = 1; $i <= 4; $i++) {
            $cats[] = Category::create(['name' => "Cat $i", 'slug' => "cat-$i"])->id;
        }

        $response = $this->actingAs($user)->post(route('jual.store'), [
            'judul'        => 'Produk Kelebihan Kategori',
            'deskripsi'    => 'Deskripsi produk',
            'harga'        => 100000,
            'stok'         => 5,
            'kategori_ids' => $cats, // 4 categories, should fail
            'photo1'       => UploadedFile::fake()->image('item.jpg'),
        ]);

        $response->assertSessionHasErrors('kategori_ids');
    }

    public function test_category_filtering_and_search(): void
    {
        $user = User::factory()->create();
        $catGaming = Category::create(['name' => 'Gaming', 'slug' => 'gaming']);
        $catOffice = Category::create(['name' => 'Office', 'slug' => 'office']);

        $gamingProduct = Product::create([
            'user_id'     => $user->id,
            'category_id' => $catGaming->id,
            'name'        => 'Headset Gaming 7.1',
            'slug'        => 'headset-gaming-71',
            'description' => 'Headset dengan surround sound',
            'price'       => 300000,
            'stock'       => 5,
            'is_active'   => true,
            'location'    => 'Bandung',
        ]);
        $gamingProduct->categories()->sync([$catGaming->id]);

        $officeProduct = Product::create([
            'user_id'     => $user->id,
            'category_id' => $catOffice->id,
            'name'        => 'Kursi Kantor Ergonomis',
            'slug'        => 'kursi-kantor-ergonomis',
            'description' => 'Kursi nyaman untuk kerja seharian',
            'price'       => 850000,
            'stock'       => 3,
            'is_active'   => true,
            'location'    => 'Jakarta',
        ]);
        $officeProduct->categories()->sync([$catOffice->id]);

        // 1. Dashboard filter by category slug
        $response = $this->actingAs($user)->get(route('dashboard', ['category' => 'gaming']));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Headset Gaming 7.1')
        );

        // 2. Instant search preview with category filtering
        $previewResponse = $this->actingAs($user)->getJson(route('dashboard.search.preview', [
            'q' => 'Headset',
            'category_id' => $catGaming->id,
        ]));
        $previewResponse->assertStatus(200);
        $previewResponse->assertJsonCount(1, 'products');
        $previewResponse->assertJsonPath('products.0.name', 'Headset Gaming 7.1');
    }

    public function test_seller_dashboard_metrics_and_orders(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $cat = Category::create(['name' => 'Hobi', 'slug' => 'hobi']);

        $product1 = Product::create([
            'user_id'     => $seller->id,
            'category_id' => $cat->id,
            'name'        => 'Gitar Akustik Yamaha',
            'slug'        => 'gitar-akustik-yamaha',
            'description' => 'Gitar kayu mahoni mulus',
            'price'       => 750000,
            'stock'       => 4,
            'is_active'   => true,
            'location'    => 'Yogyakarta',
        ]);

        $product2 = Product::create([
            'user_id'     => $seller->id,
            'category_id' => $cat->id,
            'name'        => 'Senar Gitar DAddario',
            'slug'        => 'senar-gitar-daddario',
            'description' => 'Senar gitar berkualitas',
            'price'       => 50000,
            'stock'       => 0, // Out of stock
            'is_active'   => true,
            'location'    => 'Yogyakarta',
        ]);

        // Paid order
        $order = Order::create([
            'user_id'          => $buyer->id,
            'total_price'      => 800000,
            'status'           => 'paid',
            'shipping_service' => 'reguler',
            'shipping_cost'    => 50000,
            'shipping_address' => ['name' => 'Budi', 'city' => 'Semarang'],
        ]);

        OrderItem::create([
            'order_id'       => $order->id,
            'product_id'     => $product1->id,
            'quantity'       => 1,
            'price_snapshot' => 750000,
            'subtotal'       => 750000,
        ]);

        $response = $this->actingAs($seller)->get(route('admin.show'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin')
            ->has('stats')
            ->where('stats.total_revenue', 750000)
            ->where('stats.total_orders', 1)
            ->where('stats.pending_shipment', 1)
            ->where('stats.total_products', 2)
            ->where('stats.active_products', 1)
            ->where('stats.out_of_stock_products', 1)
            ->has('recent_orders', 1)
            ->has('products', 2)
        );
    }

    public function test_shipping_cost_calculation_based_on_distance(): void
    {
        // 1. Same city (local): Jakarta Selatan & Jakarta Pusat
        $zoneLocal = ShippingService::getDistanceZone('Jakarta Selatan', 'Jakarta Pusat');
        $this->assertEquals('local', $zoneLocal);

        $localHemat = ShippingService::calculateCost('Jakarta Selatan', 'Jakarta Pusat', ShippingService::SERVICE_HEMAT);
        $localReguler = ShippingService::calculateCost('Jakarta Selatan', 'Jakarta Pusat', ShippingService::SERVICE_REGULER);
        $localExpress = ShippingService::calculateCost('Jakarta Selatan', 'Jakarta Pusat', ShippingService::SERVICE_EXPRESS);

        $this->assertEquals(6000, $localHemat);
        $this->assertEquals(10000, $localReguler);
        $this->assertEquals(18000, $localExpress);

        // 2. Same region cluster (regional): Bandung & Semarang (Jawa)
        $zoneRegional = ShippingService::getDistanceZone('Bandung', 'Semarang');
        $this->assertEquals('regional', $zoneRegional);

        $regionalHemat = ShippingService::calculateCost('Bandung', 'Semarang', ShippingService::SERVICE_HEMAT);
        $regionalReguler = ShippingService::calculateCost('Bandung', 'Semarang', ShippingService::SERVICE_REGULER);
        $regionalExpress = ShippingService::calculateCost('Bandung', 'Semarang', ShippingService::SERVICE_EXPRESS);

        $this->assertEquals(14000, $regionalHemat);
        $this->assertEquals(22000, $regionalReguler);
        $this->assertEquals(35000, $regionalExpress);

        // 3. Different region / island (inter_region): Medan (Sumatera) & Surabaya (Jawa)
        $zoneInter = ShippingService::getDistanceZone('Medan', 'Surabaya');
        $this->assertEquals('inter_region', $zoneInter);

        $interHemat = ShippingService::calculateCost('Medan', 'Surabaya', ShippingService::SERVICE_HEMAT);
        $interReguler = ShippingService::calculateCost('Medan', 'Surabaya', ShippingService::SERVICE_REGULER);
        $interExpress = ShippingService::calculateCost('Medan', 'Surabaya', ShippingService::SERVICE_EXPRESS);

        $this->assertEquals(25000, $interHemat);
        $this->assertEquals(38000, $interReguler);
        $this->assertEquals(60000, $interExpress);
    }

    public function test_checkout_with_selected_shipping_tier(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $category = Category::create(['name' => 'Gadget', 'slug' => 'gadget']);

        $product = Product::create([
            'user_id'     => $seller->id,
            'category_id' => $category->id,
            'name'        => 'Smartwatch Pro',
            'slug'        => 'smartwatch-pro',
            'description' => 'Jam tangan pintar',
            'price'       => 500000,
            'stock'       => 10,
            'is_active'   => true,
            'location'    => 'Jakarta Selatan',
        ]);

        $addressData = [
            'name'        => 'Dewi Sartika',
            'phone'       => '08123456789',
            'detail'      => 'Jl. Sudirman No 1',
            'subdistrict' => 'Kebayoran Baru',
            'city'        => 'Jakarta Selatan', // Same city -> local express = 18,000
            'province'    => 'DKI Jakarta',
            'postal_code' => '12190',
        ];

        // Choose express service in same city: cost should be 18000
        $response = $this->actingAs($buyer)->post(route('checkout.store'), [
            'product_ids'      => [$product->id],
            'is_buy_now'       => true,
            'quantities'       => [$product->id => 1],
            'shipping_service' => 'express',
            'address'          => $addressData,
        ]);

        $order = Order::where('user_id', $buyer->id)->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('orders.payment', ['id' => $order->id]));

        $this->assertEquals('express', $order->shipping_service);
        $this->assertEquals(18000, $order->shipping_cost);
        $this->assertEquals(518000, $order->total_price); // 500,000 + 18,000
    }
}

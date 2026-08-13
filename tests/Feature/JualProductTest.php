<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JualProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_submit_a_product_listing(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category = \App\Models\Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        $response = $this->actingAs($user)->post(route('jual.store'), [
            'judul' => 'iPhone 13 128GB',
            'harga' => 8500000,
            'deskripsi' => 'Kondisi mulus dan lengkap.',
            'stok' => 1,
            'kategori' => $category->id,
            'photo1' => UploadedFile::fake()->image('iphone.jpg'),
            'photo2' => UploadedFile::fake()->image('iphone2.jpg'),
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('products', [
            'name' => 'iPhone 13 128GB',
            'slug' => 'iphone-13-128gb',
            'price' => 8500000,
            'stock' => 1,
            'is_active' => true,
        ]);

        $product = Product::first();

        $this->assertNotNull($product);
        $this->assertCount(2, $product->images);

        // Assert images are stored
        foreach ($product->images as $image) {
            $this->assertDatabaseHas('product_images', [
                'product_id' => $product->id,
                'image_path' => $image->image_path,
            ]);
            $this->assertTrue(Storage::disk('public')->exists($image->image_path));
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample products
        $products = [
            [
                'name' => 'iPhone 13 Pro Max 256GB',
                'slug' => 'iphone-13-pro-max-256gb',
                'description' => 'iPhone 13 Pro Max kondisi mulus, fullset, garansi resmi iBox masih berlaku. Warna Graphite.',
                'price' => 15000000,
                'stock' => 3,
                'is_active' => true,
                'image_path' => 'products/iphone_13_pro_max.png',
            ],
            [
                'name' => 'Samsung Galaxy S22 Ultra',
                'slug' => 'samsung-galaxy-s22-ultra',
                'description' => 'Samsung S22 Ultra 12/256GB, kondisi seperti baru, lengkap dengan S-Pen.',
                'price' => 12500000,
                'stock' => 5,
                'is_active' => true,
                'image_path' => 'products/samsung_galaxy_s22_ultra.png',
            ],
            [
                'name' => 'MacBook Pro M2 14 inch',
                'slug' => 'macbook-pro-m2-14',
                'description' => 'MacBook Pro 14" M2 Chip, 16GB RAM, 512GB SSD. Masih garansi resmi Apple.',
                'price' => 28000000,
                'stock' => 2,
                'is_active' => true,
                'image_path' => 'products/macbook_pro_m2.png',
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'slug' => 'sony-wh-1000xm5',
                'description' => 'Headphone premium dengan noise cancellation terbaik. Kondisi seperti baru.',
                'price' => 4500000,
                'stock' => 10,
                'is_active' => true,
                'image_path' => 'products/sony_wh_1000xm5.png',
            ],
            [
                'name' => 'iPad Air 5th Gen 64GB',
                'slug' => 'ipad-air-5-64gb',
                'description' => 'iPad Air generasi 5 dengan M1 Chip, warna Starlight, kondisi mint.',
                'price' => 8500000,
                'stock' => 4,
                'is_active' => true,
                'image_path' => 'products/ipad_air_5.png',
            ],
        ];

        $user = \App\Models\User::first();
        $category = \App\Models\Category::first();

        foreach ($products as $productData) {
            $imagePath = $productData['image_path'];
            unset($productData['image_path']);

            $productData['user_id'] = $user->id;
            $productData['category_id'] = $category->id;
            $product = Product::create($productData);

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $imagePath,
            ]);
        }

        $sampleImages = [
            'products/iphone_13_pro_max.png',
            'products/samsung_galaxy_s22_ultra.png',
            'products/macbook_pro_m2.png',
            'products/sony_wh_1000xm5.png',
            'products/ipad_air_5.png',
        ];

        Product::factory(20)->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ])->each(function ($product) use ($sampleImages) {
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => fake()->randomElement($sampleImages),
            ]);
        });
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class JualController extends Controller
{
    public function index()
    {
        return view('jual.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'harga' => ['required', 'numeric', 'min:0'],
            'deskripsi' => ['required', 'string'],
            'photo1' => ['nullable', 'image', 'max:2048'],
            'photo2' => ['nullable', 'image', 'max:2048'],
            'photo3' => ['nullable', 'image', 'max:2048'],
            'kategori' => ['required', 'numeric', 'max:6'],
        ]);

        // Create product
        $product = Product::create([
            'user_id' => Auth::id(),
            'name' => $validated['judul'],
            'slug' => $this->generateUniqueSlug($validated['judul']),
            'description' => $validated['deskripsi'],
            'price' => $validated['harga'],
            'stock' => 1,
            'is_active' => true,
            'category_id' => $validated['kategori'],
        ]);

        // Store product images
        foreach (['photo1', 'photo2', 'photo3'] as $photoField) {
            if ($request->hasFile($photoField)) {
                $path = $request->file($photoField)->store('products', 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->route('dashboard')->with('success', 'Produk berhasil ditambahkan!');
    }

    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 2;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}

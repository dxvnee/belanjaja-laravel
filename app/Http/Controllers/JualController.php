<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class JualController extends Controller
{
    public function index()
    {
        $defaultLocation = Auth::user()->address()->first()?->city ?? '';
        $categories = Category::all();

        return Inertia::render('Jual', [
            'defaultLocation' => $defaultLocation,
            'categories'      => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $rawKategori = $request->input('kategori_ids') ?? $request->input('kategori');
        $kategoriIds = is_array($rawKategori) ? $rawKategori : [$rawKategori];
        $kategoriIds = array_slice(array_values(array_unique(array_map('intval', array_filter($kategoriIds)))), 0, 3);
        if (empty($kategoriIds)) {
            $kategoriIds = [1];
        }

        $validated = $request->validate([
            'judul'        => ['required', 'string', 'max:255'],
            'harga'        => ['required', 'numeric', 'min:0'],
            'stok'         => ['required', 'integer', 'min:1'],
            'deskripsi'    => ['required', 'string'],
            'lokasi'       => ['nullable', 'string', 'max:255'],
            'photo1'       => ['nullable', 'image', 'max:2048'],
            'photo2'       => ['nullable', 'image', 'max:2048'],
            'photo3'       => ['nullable', 'image', 'max:2048'],
            'kategori'     => ['nullable'],
            'kategori_ids' => ['nullable', 'array', 'min:1', 'max:3'],
        ]);

        $location = !empty($validated['lokasi'])
            ? $validated['lokasi']
            : (Auth::user()->address()->first()?->city ?? 'Kota Jakarta Selatan');

        $primaryCategoryId = $kategoriIds[0];

        // Create product
        $product = Product::create([
            'user_id'     => Auth::id(),
            'name'        => $validated['judul'],
            'slug'        => $this->generateUniqueSlug($validated['judul']),
            'description' => $validated['deskripsi'],
            'price'       => $validated['harga'],
            'stock'       => $validated['stok'],
            'location'    => $location,
            'is_active'   => true,
            'category_id' => $primaryCategoryId,
        ]);

        // Sync up to 3 categories to pivot table
        $product->categories()->sync($kategoriIds);

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

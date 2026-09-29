<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function show($id)
    {
        $user = Auth::user();
        $product = Product::where('id', $id)
            ->with([
                'images',
                'category',
                'categories',
                'user' => function ($q) {
                    $q->withCount(['products' => function ($sq) {
                        $sq->where('is_active', true);
                    }]);
                },
                'reviews' => function ($q) {
                    $q->with('user')->latest();
                },
            ])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->firstOrFail();

        return Inertia::render('ProductDetail', [
            'product' => $product,
            'is_owner' => $user ? $product->user_id === $user->id : false,
        ]);
    }

    public function edit($id)
    {
        $user = Auth::user();
        $product = Product::where('id', $id)->with(['images', 'categories', 'category'])->firstOrFail();

        if ($product->user_id !== $user->id) {
            return redirect()->route('product.show', ['id' => $id])->with('error', 'Anda tidak memiliki izin untuk mengedit produk ini.');
        }

        $categories = \App\Models\Category::all();

        return Inertia::render('Edit', [
            'product'    => $product,
            'categories' => $categories,
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $product = Product::where('id', $id)->firstOrFail();
        if ($product->user_id !== $user->id) {
            return redirect()->route('product.show', ['id' => $id])->with('error', 'Anda tidak memiliki izin untuk menghapus produk ini.');
        }

        $product->delete();

        return redirect()->route('jual.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $product = Product::where('id', $id)->firstOrFail();

        if ($product->user_id !== $user->id) {
            return redirect()->route('product.show', ['id' => $id])->with('error', 'Anda tidak memiliki izin untuk mengedit produk ini.');
        }

        $rawKategori = $request->input('kategori_ids') ?? $request->input('kategori');
        $kategoriIds = is_array($rawKategori) ? $rawKategori : [$rawKategori];
        $kategoriIds = array_slice(array_values(array_unique(array_map('intval', array_filter($kategoriIds)))), 0, 3);
        if (empty($kategoriIds)) {
            $kategoriIds = [$product->category_id ?: 1];
        }

        $validated = $request->validate([
            'judul'        => ['required', 'string', 'max:255'],
            'harga'        => ['required', 'numeric', 'min:0'],
            'stok'         => ['required', 'integer', 'min:0'],
            'deskripsi'    => ['required', 'string'],
            'lokasi'       => ['nullable', 'string', 'max:255'],
            'kategori'     => ['nullable'],
            'kategori_ids' => ['nullable', 'array', 'min:1', 'max:3'],
            'photo1'       => ['nullable', 'image', 'max:2048'],
            'photo2'       => ['nullable', 'image', 'max:2048'],
            'photo3'       => ['nullable', 'image', 'max:2048'],
        ]);

        $product->update([
            'name'        => $validated['judul'],
            'description' => $validated['deskripsi'],
            'price'       => $validated['harga'],
            'stock'       => $validated['stok'],
            'location'    => $validated['lokasi'] ?? $product->location,
            'category_id' => $kategoriIds[0],
        ]);

        $product->categories()->sync($kategoriIds);

        foreach (['photo1', 'photo2', 'photo3'] as $photoField) {
            if ($request->hasFile($photoField)) {
                $path = $request->file($photoField)->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->route('product.show', ['id' => $product->id])->with('success', 'Produk berhasil diperbarui!');
    }
}

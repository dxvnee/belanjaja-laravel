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
        $product = Product::where('id', $id)->with('images')->firstOrFail();
        return Inertia::render('ProductDetail', [
            'product' => $product,
            'is_owner' => $product->user_id === $user->id,
        ]);
    }

    public function edit($id)
    {
        $user = Auth::user();
        $product = Product::where('id', $id)->with('images')->firstOrFail();

        if($product->user_id !== $user->id) {
            return redirect()->route('product.show', ['id' => $id])->with('error', 'Anda tidak memiliki izin untuk mengedit produk ini.');
        }


        return Inertia::render('Edit', [
            'product' => $product,
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

        $validated = $request->validate([
            'judul'    => ['required', 'string', 'max:255'],
            'harga'    => ['required', 'numeric', 'min:0'],
            'stok'     => ['required', 'integer', 'min:0'],
            'deskripsi'=> ['required', 'string'],
            'kategori' => ['required', 'numeric', 'max:6'],
            'photo1'   => ['nullable', 'image', 'max:2048'],
            'photo2'   => ['nullable', 'image', 'max:2048'],
            'photo3'   => ['nullable', 'image', 'max:2048'],
        ]);

        $product->update([
            'name'        => $validated['judul'],
            'description' => $validated['deskripsi'],
            'price'       => $validated['harga'],
            'stock'       => $validated['stok'],
            'category_id' => $validated['kategori'],
        ]);

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

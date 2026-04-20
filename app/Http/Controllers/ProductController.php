<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function show($id)
    {
        $product = Product::where('id', $id)->with('images')->firstOrFail();
        return Inertia::render('ProductDetail', [
            'product' => $product,
        ]);
    }
}

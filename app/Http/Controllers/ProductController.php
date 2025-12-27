<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('status', 'active')
                    ->paginate(20);

        return Inertia::render('Products/Index', [
            'products' => $products
        ]);
    }

    public function show(Product $product)
    {
        if ($product->status !== 'active') {
            abort(404, 'Product not available');
        }

        return Inertia::render('Products/Show', [
            'product' => $product
        ]);
    }
}

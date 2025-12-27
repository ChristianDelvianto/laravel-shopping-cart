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

        // Show other products for user (using custom algorithm)
        $recommended = Product::where('id', '!=', $product->id)
                    ->where('status', 'active')
                    ->where('stock_quantity', '>', 0)
                    ->inRandomOrder()
                    ->limit(20)
                    ->get();

        return Inertia::render('Products/Show', [
            'product' => $product,
            'recommended' => $recommended
        ]);
    }
}

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

    public function show(Request $request, Product $product)
    {
        $currentUser = $request->user();

        if ($product->status !== 'active') {
            abort(404, 'Product not available');
        }

        $product->load([
            'cartItems' => function ($query) use ($currentUser) {
                $query->whereHas('cart', function ($q) use ($currentUser) {
                    $q->where('user_id', $currentUser->id);
                });
            }
        ]);

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

<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotifyStockRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Jobs\NotifyProductStockAvailability;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        // Get newest products
        $products = Product::where('status', 'active')
                    ->where('stock_quantity', '>', 0)
                    ->latest()
                    ->paginate(20);

        return Inertia::render('Products/Index', [
            'products' => $products
        ]);
    }

    /**
     * 
     */
    public function notifyStockAvailability(NotifyStockRequest $request, Product $product)
    {
        if ($product->stock_quantity > 0) { // Stock available
            return back();
        }
        
        if ($request->validated('version') !== $product->version) { // Version mismatch
            return back()->withErrors([
                'message' => 'Something went wrong, please try again later'
            ]);
        }

        // Load toNotify relationship
        $product->load([
            'toNotify' => function ($query) use ($product, $request) {
                $query->where('user_id', $request->user()->id)
                ->where('product_version', $product->version);
            }
        ]);

        // Add $currentUser to notify when product stock available
        if ($product->toNotify->isEmpty()) {
            $product->toNotify()->attach($request->user()->id, [
                'product_version' => $product->version,
                'created_at' => now()
            ]);
        }

        /**
         * Just return success whether user notify record already exists / not
         * This reduce frontend complexity
         */

        Inertia::flash('success', 'Notification enabled');

        return back();
    }

    /**
     * Show a product and recommended products
     */
    public function show(Request $request, Product $product)
    {
        // Product is inactive
        if ($product->status !== 'active') {
            return back()->withErrors([
                'message' => 'Opps.. Product not available'
            ]);
        }

        $product->load([
            'cartItems' => function ($query) use ($request) {
                $query->whereHas('cart', function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                });
            }
        ]);

        // Conditional eager loading
        if ($product->stock_quantity < 1) {
            $product->load([
                'toNotify' => function ($query) use ($product, $request) {
                    $query->where('user_id', $request->user()->id)
                    ->where('product_version', $product->version);
                }
            ]);
        }

        // Show other products for user (We can also use custom algorithm)
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

    /**
     * Update a product (Admin only)
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $admin = $request->user();

        // Update process here...

        if ($request->stock_quantity > 0 && $product->stock_quantity < 1) {
            // Dispatch a job to notify users that stock available
            dispatch(new NotifyProductStockAvailability($product));
        }

        Inertia::flash('success', 'Notification enabled');

        return back();
    }
}

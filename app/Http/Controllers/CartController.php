<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user();

        $items = $currentUser
                ->cartItems()
                ->with('product')
                ->whereHas('product', function ($query) {
                    $query->where('status', 'active');
                })
                ->paginate(20);

        return Inertia::render('Cart/Index', [
            'items' => $items
        ]);
    }

    public function removeFromCart(Request $request, CartItem $cartItem)
    {
        $cartItem->delete();

        Inertia::flash('success', 'Product removed from cart');

        return back();
    }

    public function updateCartQuantity(Request $request, CartItem $cartItem)
    {
        $currentUser = $request->user();

        $cartItem->load(['cart', 'product']);

        if ($currentUser->id !== $cartItem->cart->user_id) {
            abort(400, 'Sorry, this cart does not belong to you');
        }

        // 

        Inertia::flash('success', 'Cart updated successfully');

        return back();
    }

    public function upsertProductToCart(Request $request, Product $product)
    {
        abort(403, 'Product is out of stock');

        $currentUser = $request->user();

        // Check product status
        if ($product->status !== 'active') {
            abort(404, 'Product not available');
        }

        // Check product stock quantity
        if ($product->stock_quantity < 1) {
            abort(403, 'Product is out of stock');
        }

        $validated = $request->validate([
            'count' => ['required', 'integer', 'min:1', "max:{$product->stock_quantity}"]
        ]);

        $cart = $currentUser->cart;

        try {
            $successMessage = DB::transaction(function () use ($cart, $product, $validated): string {
                $product = Product::where('id', $product->id)
                            ->where('status', 'active')
                            ->lockForUpdate()
                            ->firstOrFail();

                // Validate stock quantity again
                if ($product->stock_quantity < 1) {
                    throw new Exception('Product is out of stock', 403);
                }

                $cartItem = $cart->items()
                            ->where('product_id', $product->id)
                            ->lockForUpdate()
                            ->first();

                if ($validated['count'] > $product->stock_quantity) {
                    throw ValidationException::withMessages([
                        'count' => 'Not enough stock available',
                    ]);
                }

                if ($cartItem) {
                    $cartItem->update(['quantity' => $validated['count']]);

                    return 'Cart quantity updated';
                }
                
                // Create new cart item
                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $validated['count'],
                ]);

                return 'Product added to cart';
            });

            Inertia::flash('success', $successMessage);
        } catch (Exception $e) {
            throw $e;
        }

        return back();
    }
}

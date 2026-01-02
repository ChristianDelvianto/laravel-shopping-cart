<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Requests\UpsertCartItemRequest;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index(Request $request)
    {
        // Get user cart items with product
        $items = $request->user()->cartItems()
                ->with('product')
                ->get();

        return Inertia::render('Cart/Index', [
            'items' => $items
        ]);
    }

    /**
     * Checkout all cart items
     */
    public function checkoutItems(Request $request, CartService $cartService)
    {
        try {
            $cartService->checkout($request->user()->id);

            Inertia::flash('success', 'Your order has been created');
        } catch (Exception $e) {
            logger()->error('Checkout failed', [
                'error' => $e
            ]);

            return back()->withErrors([
                'message' => 'Checkout failed. Please try again.',
            ]);
        }

        return back();
    }

    /**
     * Remove an item from cart
     */
    public function removeCartItem(Request $request, CartItem $cartItem, CartService $cartService)
    {
        $cartItem->load('cart');

        // The cart item does not belong to user
        if ($request->user()->id !== $cartItem->cart->user_id) {
            return back()->withErrors([
                'message' => 'Sorry, this cart item does not belong to you'
            ]);
        }

        try {
            $cartService->deleteItem($cartItem);

            Inertia::flash('success', 'Item deleted from cart');
        } catch (Exception $e) {
            logger()->error('Error occured when removing cart item', [
                'error' => $e
            ]);

            return back()->withErrors([
                'message' => 'Something went wrong, please try again'
            ]);
        }

        return back();
    }

    /**
     * Update cart's item quantity
     */
    public function updateCartQuantity(UpdateCartItemRequest $request, CartItem $cartItem, CartService $cartService)
    {
        $cartItem->load(['cart', 'product']);

        // The cart item does not belong to user
        if ($request->user()->id !== $cartItem->cart->user_id) {
            return back()->withErrors(['message' => 'Sorry, this cart does not belong to you']);
        }

        try {
            $cartService->updateQuantity($cartItem, $request->validated('count'));

            Inertia::flash('success', 'Cart updated successfully');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            logger()->error('Failed update cart item quantity', [
                'error' => $e
            ]);

            return back()->withErrors(['message' => 'Unable to update cart, please try again']);
        }

        return back();
    }

    /**
     * Update or insert new product into user's cart
     */
    public function upsertProductToCart(UpsertCartItemRequest $request, Product $product, CartService $cartService)
    {
        $cart = $request->user()->cart;

        try {
            // Transaction will return success message for flash data
            $message = $cartService->upsertItem($cart, $product, $request->validated('count'));

            Inertia::flash('success', $message);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            return back()->withErrors(['message' => 'Something went wrong, please try again']);
        }

        return back();
    }
}

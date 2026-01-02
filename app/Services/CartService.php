<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Jobs\NotifyLowStockQuantity;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function checkout(int $userId): void
    {
        DB::transaction(function () use ($userId) {
            $cartItems = CartItem::whereHas('cart', function ($query) use ($userId) {
                            $query->where('user_id', $userId);
                        })
                        ->lockForUpdate()
                        ->get();

            if ($cartItems->isEmpty()) {
                throw new Exception('No items in your cart');
            }

            $order = Order::create([
                'user_id' => $userId,
                'status' => 'paid', // For the sake of this simple app, we make it 'paid'
                'subtotal_amount' => 0,
            ]);

            $subtotal = 0;

            foreach ($cartItems as $item) {
                $product = Product::where('id', $item->product_id)
                            ->lockForUpdate()
                            ->firstOrFail();

                if ($product->status !== 'active') {
                    throw new Exception('Oops.. Product not available');
                }

                if ($item->quantity > $product->stock_quantity) {
                    throw new Exception('Sorry, but product is out of stock');
                }

                $total = $product->price * $item->quantity;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'unit_name' => $product->name,
                    'unit_price' => $product->price,
                    'total_price' => $total,
                ]);

                $product->update(['stock_quantity' => $product->stock_quantity - $item->quantity,]);

                // Notify low stock
                if ($product->stock_quantity <= config('app.stock_threshold')) {
                    dispatch(new NotifyLowStockQuantity($product, $product->stock_quantity));
                }

                $item->delete();

                $subtotal += $total;
            }

            $order->update(['subtotal_amount' => $subtotal]);
        }, 3);
    }

    public function deleteItem(CartItem $cartItem): void
    {
        DB::transaction(function () use ($cartItem) {
            $cartItem = CartItem::where('id', $cartItem->id)
                        ->lockForUpdate()
                        ->first();

            if (!$cartItem) {
                throw new Exception('Cart item not found', 404);
            }

            $cartItem->delete();
        }, 3);
    }

    public function updateQuantity(CartItem $cartItem, int $quantity): void
    {
        DB::transaction(function () use ($cartItem, $quantity) {
            // Lock cart item
            $item = CartItem::where('id', $cartItem->id)
                    ->lockForUpdate()
                    ->firstOrFail();

            // Lock product
            $product = Product::where('id', $item->product_id)
                        ->lockForUpdate()
                        ->firstOrFail();

            if ($product->status !== 'active') {
                throw new Exception('Oops.. Product not available');
            }

            if ($quantity > $product->stock_quantity) {
                throw ValidationException::withMessages([
                    'count' => 'Not enough stock available',
                ]);
            }

            $item->update(['quantity' => $quantity]);
        }, 3);
    }

    public function upsertItem(Cart $cart, Product $product, int $quantity): string
    {
        return DB::transaction(function () use ($cart, $product, $quantity): string {
            $product = Product::where('id', $product->id)
                        ->lockForUpdate()
                        ->firstOrFail();

            if ($product->status !== 'active') {
                throw new Exception('Oops.. Product not available');
            }

            if ($quantity > $product->stock_quantity) {
                throw ValidationException::withMessages([
                    'count' => 'Not enough stock available',
                ]);
            }

            $cartItem = CartItem::where('cart_id', $cart->id)
                        ->where('product_id', $product->id)
                        ->lockForUpdate()
                        ->first();

            if ($cartItem) {
                $cartItem->update(['quantity' => $quantity]);

                return 'Cart item updated';
            }

            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);

            return 'Product added to cart';
        }, 3);
    }
}

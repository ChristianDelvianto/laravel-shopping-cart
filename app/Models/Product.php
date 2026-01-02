<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'status',
        'name',
        'price',
        'stock_quantity',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock_quantity' => 'integer',
            'version' => 'integer'
        ];
    }

    protected static function booted()
    {
        static::updating(function ($model) {
            if ($model->isDirty('stock_quantity')) {
                $oldValue = $model->getOriginal('stock_quantity');
                $newValue = $model->stock_quantity;

                // When stock available, update version
                if ($oldValue < 1 && $newValue >= 1) {
                    $model->version++;
                }
            }
        });
    }

    public function getPriceAttribute($value): float
    {
        return $value / 100;
    }

    public function carts()
    {
        return $this->belongsToMany(Cart::class, 'cart_items', 'product_id', 'cart_id', 'id', 'id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'product_id', 'id');
    }

    public function toNotify()
    {
        return $this->belongsToMany(User::class, 'product_notify', 'product_id', 'user_id', 'id', 'id');
    }
}

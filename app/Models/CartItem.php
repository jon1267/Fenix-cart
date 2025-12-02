<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'quantity'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->product->price,
        );
    }

    protected function sum(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->product->price * $this->quantity,
        );
    }

}

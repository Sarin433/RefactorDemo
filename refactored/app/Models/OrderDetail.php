<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'detail_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'product_number',
        'quantity',
        'unit_price',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity'   => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    /**
     * Calculate line subtotal.
     */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->unit_price * $this->quantity;
    }

    /**
     * Relationships
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_number', 'order_number');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_number', 'product_number');
    }
}

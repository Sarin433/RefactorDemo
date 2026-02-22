<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'product_number';

    /**
     * The primary key type.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_number',
        'name',
        'price',
        'stock_quantity',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price'          => 'decimal:2',
            'stock_quantity' => 'integer',
        ];
    }

    /**
     * Relationships
     */
    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'product_number', 'product_number');
    }
}

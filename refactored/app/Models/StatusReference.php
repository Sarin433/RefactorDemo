<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatusReference extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'status_references';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'status_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'status_name',
    ];

    /**
     * Relationships
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'status_id', 'status_id');
    }
}

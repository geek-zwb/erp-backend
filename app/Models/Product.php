<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'unit_id',
        'type_id',
        'weight',
        'description',
        'picture',
        'note'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function warehouses() {
        return $this->belongsToMany('App\Models\Warehouse')
            ->withPivot('inventory')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type() {
        return $this->belongsTo('App\Models\Type');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit() {
        return $this->belongsTo('App\Models\Unit');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function purchases() {
        return $this->belongsToMany('App\Models\Purchase')
            ->withPivot('count', 'price')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function orders() {
        return $this->belongsToMany('App\Models\Order')
            ->withPivot('count', 'price')
            ->withTimestamps();
    }
}

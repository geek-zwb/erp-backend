<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_code',
        'customer_id',
        'status',
        'delivery_date',
        'delivery_code',
        'delivery_company',
        'delivery_amount',
        'note',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer() {
        return $this->belongsTo('App\Models\Customer');
    }

    public function products() {
        return $this->belongsToMany('App\Models\Product')
            ->withPivot('count', 'price', 'status', 'returns_count')
            ->withTimestamps();
    }
}

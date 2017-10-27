<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id',
        'delivery_code',
        'invoice_date',
        'invoice_code',
        'invoice_amount',
        'arrears',
        'note'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products() {
        return $this->belongsToMany('App\Models\Product')
            ->withPivot('count', 'price')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|mixed
     */
    public function supplier() {
        return $this->belongsTo('App\Models\Supplier');
    }
}

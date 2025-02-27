<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDiscount extends Model
{
    protected $fillable = [
        'product_id',
        'price',
        'discount_name',
        'start_date',
        'end_date',
        'min_qty',
        'discount_type',
        'discount_amt',
        'is_active',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

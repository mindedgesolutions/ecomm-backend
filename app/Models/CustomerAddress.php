<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'mobile',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'pincode',
        'landmark',
        'type',
        'is_default',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

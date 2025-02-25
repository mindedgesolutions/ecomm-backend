<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $fillable = [
        'user_id',
        'slug',
        'first_name',
        'last_name',
        'avatar',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

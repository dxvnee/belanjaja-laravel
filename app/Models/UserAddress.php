<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'phone_number',
        'address',
        'full_address',
        'province',
        'city',
        'disctrict',
        'postal_code',
        'lat',
        'lng',
        'is_default'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

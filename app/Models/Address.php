<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'name',
        'province',
        'city',
        'subdistrict',
        'postal_code',
        'detail',
        'phone',
        'user_id',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}

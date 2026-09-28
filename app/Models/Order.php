<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total_price',
        'status',
        'snap_token',
        'payment_type',
        'shipping_address',
        'tracking_number',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function items(){
        return $this->hasMany(OrderItem::class);
    }

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
        ];
    }
}

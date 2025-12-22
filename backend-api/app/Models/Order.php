<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'symbol',
        'side',
        'price',
        'amount',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A buy order could be linked to multiple trades
    public function tradesAsBuyer()
    {
        return $this->hasMany(Trade::class, 'buy_order_id');
    }

    // A sell order could be linked to multiple trades
    public function tradesAsSeller()
    {
        return $this->hasMany(Trade::class, 'sell_order_id');
    }
}

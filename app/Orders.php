<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model 
{
    protected $table = 'orders';

    public function customer()
    {
        return $this->belongsTo('App\Customers');
    }

    public function order_item()
    {
        return $this->hasOne('App\OrderItems');
    }

    public function payment()
    {
        return $this->hasOne('App\Payments', 'foreign_key', 'order_id');
    }
}

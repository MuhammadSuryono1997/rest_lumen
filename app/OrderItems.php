<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model 
{
    protected $table = 'order_items';
    protected $timestamp = false;

    public function product()
    {
        return $this->belongsTo('App\Products');
    }

    public function order()
    {
        return $this->belongsTo('App\Orders');
    }
}

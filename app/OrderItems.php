<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model 
{
    protected $table = 'order_items';

    public function product()
    {
        return $this->belongsTo('App\Products');
    }

    public function order()
    {
        return $this->belongsTo('App\Orders');
    }
}

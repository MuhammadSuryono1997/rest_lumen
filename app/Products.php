<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Products extends Model 
{
    protected $table = 'products';

    public function order_item()
    {
        return $this->hasOne('App\OrderItems','foreign_key','product_id');
    }
}

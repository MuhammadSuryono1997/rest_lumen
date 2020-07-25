<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customers extends Model 
{
    protected $table = 'customers';

    public function order()
    {
        return $this->hasMany('App\Orders', 'user_id');
    }
}

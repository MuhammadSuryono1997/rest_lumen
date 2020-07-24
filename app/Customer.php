<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customers extends Model 
{
    protected $table = 'customers';

    public function order()
    {
        return $this->hasOne('App\Orders');
    }
}

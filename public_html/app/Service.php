<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'type','storeId', 'name', 'duration', 'icon', 'status', 'price'
    ];
}

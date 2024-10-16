<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'userId', 'storeId', 'sortIndex', 'PIN', 'hours', 'dayOff', 'status'
    ];
}

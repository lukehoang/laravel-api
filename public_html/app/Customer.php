<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'storeId', 'fname', 'lname', 'email', 'phone', 'visited', 'lastVisited', 'fav_staffId'
    ];
}

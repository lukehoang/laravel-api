<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SignInSheet extends Model
{
    protected $fillable = [
        'storeId', 'staffId', 'staff_fname', 'customer_name', 'customer_phone', 'date', 'note', 'staff_by_customer', 'service_by_customer', 'arrived_time', 'checked', 'status'
    ];
}

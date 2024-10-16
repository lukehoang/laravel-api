<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'storeId', 'staffId', 'customerId', 'serviceIds', 'date', 'start', 'end', 'note', 'createdBy', 'modifiedBy', 'price', 'sale'
    ];
}

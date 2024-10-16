<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreLog extends Model
{
    protected $fillable = [
        'app_type', 'storeId', 'appointmentId', 'userIds', 'text', 'appointment_log'
    ];
}

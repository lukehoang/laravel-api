<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'storeId', 'amount', 'receipt_url'
    ];
}

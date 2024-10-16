<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Store extends Authenticatable
{
    use Notifiable,HasApiTokens;

    protected $guarded = ['store'];

    protected $fillable = [
        'ownerId', 'name', 'username', 'password', 'street', 'address2', 'city', 'state', 'zip', 'phone', 'email', 'permalink', 'hours', 'status', 'app_version', 'expiration', 'packageId', 'stripe_id', 'sub_id'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function findForPassport($identifier) {
        return $this->orWhere('email', $identifier)->orWhere('username', $identifier)->first();
}
}

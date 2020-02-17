<?php

namespace ALajusticia\AuthTracker\Tests;

use ALajusticia\AuthTracker\Traits\AuthTracking;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Airlock\HasApiTokens;

class User extends Authenticatable
{
    use AuthTracking, HasApiTokens;

    protected $fillable = [
        'id', 'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}

<?php

namespace ALajusticia\AuthTracker\Tests;

use ALajusticia\AuthTracker\Traits\AuthTracking;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Airlock\HasApiTokens;

class AirlockUser extends Authenticatable
{
    use AuthTracking, HasApiTokens;

    protected $table = 'users';

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

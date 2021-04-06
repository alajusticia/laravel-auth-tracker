<?php

namespace ALajusticia\AuthTracker\Tests;

use ALajusticia\AuthTracker\Tests\Database\Factories\PassportUserFactory;
use ALajusticia\AuthTracker\Traits\AuthTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class PassportUser extends Authenticatable
{
    use AuthTracking, HasApiTokens, HasFactory;

    protected $fillable = [
        'id', 'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return PassportUserFactory::new();
    }
}

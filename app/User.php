<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class User extends Authenticatable implements \Tymon\JWTAuth\Contracts\JWTSubject
{
    use HasRolesAndAbilities;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'dn', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey(); // Eloquent Model method
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}

<?php

namespace App;

use Silber\Bouncer\Database\HasRolesAndAbilities;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
        $claims = [];
        $claims['permissions'] = [];

        // Check Role of user
        if ($this->can('read', Site::class)) {
            $claims['permissions']['read_sites'] = true;
        }

        // Check Role of user
        if ($this->can('read', Didblock::class)) {
            $claims['permissions']['read_didblock'] = true;
        }

        // Check Role of user
        if ($this->can('read', Cucmsiteconfigs::class)) {
            $claims['permissions']['read_cucmreports'] = true;
        }

        $claims['permissions']['jack'] = true;

        return $claims;
    }
}

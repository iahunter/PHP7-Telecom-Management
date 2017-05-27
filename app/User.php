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
        'username', 'dn', 'password', 'userprincipalname',
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
        // Add custom properties to the token here. This can be used to send the browser its permissions.
        $claims = [];
        $claims['user'] = $this->id;
        $claims['permissions'] = [];
        $claims['permissions']['read'] = [];
        $claims['permissions']['create'] = [];
        $claims['permissions']['update'] = [];
        $claims['permissions']['delete'] = [];

        // Did Block Permissions

        if ($this->can('read', Didblock::class)) {
            $claims['permissions']['read']['Didblock'] = true;
        }

        if ($this->can('create', Didblock::class)) {
            $claims['permissions']['create']['Didblock'] = true;
        }

        if ($this->can('update', Didblock::class)) {
            $claims['permissions']['update']['Didblock'] = true;
        }

        if ($this->can('delete', Didblock::class)) {
            $claims['permissions']['delete']['Didblock'] = true;
        }

        if ($this->can('read', Did::class)) {
            $claims['permissions']['read']['Did'] = true;
        }

        if ($this->can('update', Did::class)) {
            $claims['permissions']['update']['Did'] = true;
        }

        // Site Permissions

        if ($this->can('read', Site::class)) {
            $claims['permissions']['read']['Site'] = true;
        }

        if ($this->can('create', Site::class)) {
            $claims['permissions']['create']['Site'] = true;
        }

        if ($this->can('update', Site::class)) {
            $claims['permissions']['update']['Site'] = true;
        }

        if ($this->can('delete', Site::class)) {
            $claims['permissions']['delete']['Site'] = true;
        }

        // Phone Plan Permissions

        if ($this->can('read', Phoneplan::class)) {
            $claims['permissions']['read']['Phoneplan'] = true;
        }

        if ($this->can('create', Phoneplan::class)) {
            $claims['permissions']['create']['Phoneplan'] = true;
        }

        if ($this->can('update', Phoneplan::class)) {
            $claims['permissions']['update']['Phoneplan'] = true;
        }

        if ($this->can('delete', Phoneplan::class)) {
            $claims['permissions']['delete']['Phoneplan'] = true;
        }

        // Phone Permissions

        if ($this->can('read', Phone::class)) {
            $claims['permissions']['read']['Phone'] = true;
        }

        if ($this->can('create', Phone::class)) {
            $claims['permissions']['create']['Phone'] = true;
        }

        if ($this->can('update', Phone::class)) {
            $claims['permissions']['update']['Phone'] = true;
        }

        if ($this->can('delete', Phone::class)) {
            $claims['permissions']['delete']['Phone'] = true;
        }

        // Cucmsiteconfigs Permissions

        if ($this->can('read', Cucmsiteconfigs::class)) {
            $claims['permissions']['read']['Cucmsiteconfigs'] = true;
        }

        // Cucm Permissions

        if ($this->can('read', Cucmclass::class)) {
            $claims['permissions']['read']['Cucmclass'] = true;
        }

        if ($this->can('create', Cucmclass::class)) {
            $claims['permissions']['create']['Cucmclass'] = true;
        }

        if ($this->can('update', Cucmclass::class)) {
            $claims['permissions']['update']['Cucmclass'] = true;
        }

        if ($this->can('delete', Cucmclass::class)) {
            $claims['permissions']['delete']['Cucmclass'] = true;
        }

        // Calls Graph Permissions

        if ($this->can('read', Calls::class)) {
            $claims['permissions']['read']['Calls'] = true;
        }

        // Cucm Permissions

        if ($this->can('read', Cupi::class)) {
            $claims['permissions']['read']['Cupi'] = true;
        }

        // Sonus5k Permissions

        if ($this->can('read', Sonus5k::class)) {
            $claims['permissions']['read']['Sonus5k'] = true;
        }

        if ($this->can('read', Sonus5kCDR::class)) {
            $claims['permissions']['read']['Sonus5kCDR'] = true;
        }

        // Telecom Infrastructure Permissions

        if ($this->can('read', TelecomInfrastructure::class)) {
            $claims['permissions']['read']['TelecomInfrastructure'] = true;
        }

        if ($this->can('create', TelecomInfrastructure::class)) {
            $claims['permissions']['create']['TelecomInfrastructure'] = true;
        }

        if ($this->can('update', TelecomInfrastructure::class)) {
            $claims['permissions']['update']['TelecomInfrastructure'] = true;
        }

        if ($this->can('delete', TelecomInfrastructure::class)) {
            $claims['permissions']['delete']['TelecomInfrastructure'] = true;
        }

        if ($this->can('read', \Spatie\Activitylog\Models\Activity::class)) {
            $claims['permissions']['read']['ActivityLog'] = true;
        }

        return $claims;
    }
}

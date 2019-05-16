<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return redirect('ui');
});

Route::get('/ldap', function (Illuminate\Http\Request $request) {
    require_once base_path().'/vendor/adldap/adldap/src/adLDAP.php';

    $ldap = new \adLDAP\adLDAP([
        'base_dn'            => env('LDAP_BASEDN'),
        'admin_username'     => env('LDAP_USER'),
        'admin_password'     => env('LDAP_PASS'),
//        'domain_controllers' => [env('LDAP_HOST')],
        'domain_controllers' => ['127.0.0.1'],
        'ad_port'            => env('LDAP_PORT'),
        'account_suffix'     => '@'.env('LDAP_DOMAIN'),
    ]);

    if ($request->get('user')) {
        $user = $request->get('user');
        $stuff = $ldap->user()->info($user, ['*']);
        dd($stuff);
    } elseif ($request->get('group')) {
        $group = $request->get('group');
        $stuff = $ldap->group()->info($group, ['*']);
        dd($stuff);
    } else {
        return 'Your query must have a user= or group=';
    }
    /**/
});

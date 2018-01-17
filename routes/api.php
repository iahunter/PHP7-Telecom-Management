<?php


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    $api->get('hello', function () {
        return "Hello world - demo app!\n";
    });

    /**
     * @SWG\Info(title="Telecom Management - API Documentation", version="1.0")
     **/

    // Disallow users to list users and get userinfo from API.
    //$api->get('listusers', 'App\Http\Controllers\Auth\AuthController@listusers');

    // Get your user info.
    $api->get('userinfo', 'App\Http\Controllers\Auth\AuthController@userinfo');

    // Auth routes
    require __DIR__.'/api.auth.php';

    // Page Logging
    require __DIR__.'/api.page.php';

    // Did and Didblock routes
    require __DIR__.'/api.did.php';

    // Site Planning Routes
    require __DIR__.'/api.planning.site.php';
    require __DIR__.'/api.planning.site.phoneplan.php';
    require __DIR__.'/api.planning.site.phones.php';

    // Site Migraiton Routes
    require __DIR__.'/api.migration.site.php';

    // CUCM AXL Routes
    require __DIR__.'/api.cucm.php';
    require __DIR__.'/api.cucm.site.php';
    require __DIR__.'/api.cucm.phone.php';
    require __DIR__.'/api.cucm.ctiroutepoint.php';
    require __DIR__.'/api.cucm.line.php';
    require __DIR__.'/api.cucm.site.migration.php';

    // CUCM Realtime API Routes
    require __DIR__.'/api.cucm.ris.php';
	
	// CUCM CDR and CMR Routes
    require __DIR__.'/api.cucm.cdrs.php';

    // Phone MACD Routes
    require __DIR__.'/api.phonemacd.php';

    // Unity Connection routes
    require __DIR__.'/api.cupi.php';

    // LDAP routes
    require __DIR__.'/api.ldap.php';

    // Sonus routes
    require __DIR__.'/api.sonus5k.php';
    require __DIR__.'/api.sonus5k.cdrs.php';

    // West 911Enable Routes
    require __DIR__.'/api.egw.php';

    // SBC Call History
    require __DIR__.'/api.calls.php';

    // Gateway Call History
    require __DIR__.'/api.cucm.gatewaycalls.php';

    // CUCM Reports
    require __DIR__.'/api.cucmreports.php';

    // Telecom Infrastructure
    require __DIR__.'/api.telecom.infrastructure.php';

    // Monitoring
    require __DIR__.'/api.ping.php';
});

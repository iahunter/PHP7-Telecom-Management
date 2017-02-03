<?php

    // User authentication routes
    $options = [
                   'prefix'     => 'authenticate',
                   'namespace'  => 'App\Http\Controllers\Auth',
                   'middleware' => 'api.throttle',
                   'limit'      => 1000,
                   'expires'    => 1,
               ];
    $api->group($options, function ($api) {
        /**
         * @SWG\Get(
         *     path="/telephony/api/authenticate",
         *     tags={"Authentication"},
         *     summary="Get JSON web token by TLS client certificate authentication",
         *     @SWG\Response(
         *         response=200,
         *         description="Authentication succeeded",
         *         ),
         *     ),
         * )
         **/
        $api->get('', 'AuthController@authenticate');
		
		/**
         * @SWG\Get(
         *     path="/telephony/api/authenticate/renew",
         *     tags={"Authentication"},
         *     summary="Get JSON web token by TLS client certificate authentication",
         *     @SWG\Response(
         *         response=200,
         *         description="Authentication succeeded",
         *         ),
         *     ),
         * )
         **/
        $api->get('/renew', 'AuthController@renew');
		
        /**
         * @SWG\Post(
         *     path="/telephony/api/authenticate",
         *     tags={"Authentication"},
         *     summary="Get JSON web token by LDAP user authentication",
         *     @SWG\Parameter(
         *         name="username",
         *         in="formData",
         *         description="LDAP username",
         *         required=true,
         *         type="string"
         *     ),
         *     @SWG\Parameter(
         *         name="password",
         *         in="formData",
         *         description="LDAP password",
         *         required=true,
         *         type="string"
         *     ),
         *     @SWG\Response(
         *         response=200,
         *         description="Authentication succeeded",
         *         ),
         *     ),
         * )
         **/
        $api->post('', 'AuthController@authenticate');
    });

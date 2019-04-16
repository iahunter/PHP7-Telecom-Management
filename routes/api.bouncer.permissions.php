<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/bouncer/permissions",
     *     tags={"Admin - Bouncer Permissions"},
     *     summary="List User Permissions",
     *     description="",
     *     operationId="getUsersPermissions",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Unauthorized user",
     *     ),
     * )
     **/
    $api->get('bouncer/permissions', 'App\Http\Controllers\BouncerPermissionsController@getUsersPermissions');

<?php


    /********************************
       CUCM Reports API
    ********************************/

    /**
     * @SWG\Get(
     *     path="/telephony/api/reports/sites",
     *     tags={"CUCM Reports"},
     *     summary="List of DID Blocks for authorized user",
     *     description="",
     *     operationId="listSites",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->get('/reports/sites', 'App\Http\Controllers\CucmReportsController@siteE911TrunkingReport');

<?php

    /**
     * @SWG\Put(
     *     path="/telephony/api/cucm/line",
     *     tags={"Management - CUCM - Line CFA - Provisioning"},
     *     summary="Update Line CFA in CUCM",
     *     description="",
     *     operationId="createSite",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="pattern",
     *         in="formData",
     *         description="10 Digit Number",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="partition",
     *         in="formData",
     *         description="Example: Global-All-Lines - Default for all Lines",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="sitecode",
     *         in="formData",
     *         description="Sitecode being used",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="cfa_destination",
     *         in="formData",
     *         description="Call Forward Destination - 10 Digit number only",
     *         required=false,
     *         type="string"
     *     ),
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
    $api->put('cucm/line', 'App\Http\Controllers\CucmLine@updateLineCFWAbyPattern');

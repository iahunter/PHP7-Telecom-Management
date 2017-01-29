<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/sbc/callstats/listcallstats",
     *     tags={"SBC - History"},
     *     summary="List Call Stats",
     *     description="",
     *     operationId="listcallstats",
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
    $api->get('sbc/callstats/listcallstats', 'App\Http\Controllers\Callcontroller@listcallstats');

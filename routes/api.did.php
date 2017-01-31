<?php


    /********************************
        DID Block App routes
    ********************************/
    /**
     * @SWG\Post(
     *     path="/telephony/api/didblock",
     *     tags={"Did Block"},
     *     summary="Create DID Block",
     *     description="",
     *     operationId="createDidblock",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="formData",
     *         description="Name of New Block",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="carrier",
     *         in="formData",
     *         description="Carrier Information",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="comment",
     *         in="formData",
     *         description="Comment",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="country_code",
     *         in="formData",
     *         description="Country Code",
     *         required=true,
     *         type="integer"
     *     ),
     *	   @SWG\Parameter(
     *         name="start",
     *         in="formData",
     *         description="Range Start",
     *         required=true,
     *         type="integer"
     *     ),
     *	   @SWG\Parameter(
     *         name="end",
     *         in="formData",
     *         description="Range End",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="type",
     *         in="formData",
     *         description="public or private",
     * 		   enum={"public", "private"},
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="reserved",
     *         in="formData",
     *         description="Automation Only",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->post('didblock', 'App\Http\Controllers\Didcontroller@createDidblock');

    /**
     * @SWG\Get(
     *     path="/telephony/api/didblock/{id}",
     *     tags={"Did Block"},
     *     summary="Get DID Block by ID",
     *     description="",
     *     operationId="getDidblock",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of block id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->get('didblock/{id}', 'App\Http\Controllers\Didcontroller@getDidblock');

    /**
     * @SWG\Get(
     *     path="/telephony/api/didblock",
     *     tags={"Did Block"},
     *     summary="List of DID Blocks for authorized user",
     *     description="",
     *     operationId="listDidblock",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->get('didblock', 'App\Http\Controllers\Didcontroller@listDidblock');

    /**
     * @SWG\Get(
     *     path="/telephony/api/didblock/number/{number}",
     *     tags={"Did Block"},
     *     summary="Get DIDBock by start number search for authorized user",
     *     description="",
     *     operationId="searchDidblocks",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="number",
     *         in="path",
     *         description="Search for Number",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->get('didblock/number/{number}', 'App\Http\Controllers\Didcontroller@searchDidblock');

    /**
     * @SWG\Put(
     *     path="/telephony/api/didblock/{id}",
     *     tags={"Did Block"},
     *     summary="Update DID Block by ID for authorized user",
     *     description="",
     *     operationId="updateDidblock",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of block id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         in="formData",
     *         description="Name of New Block",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="carrier",
     *         in="formData",
     *         description="Carrier Information",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="comment",
     *         in="formData",
     *         description="Comment",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->put('didblock/{id}', 'App\Http\Controllers\Didcontroller@updateDidblock');

    /**
     * @SWG\Delete(
     *     path="/telephony/api/didblock/{id}",
     *     tags={"Did Block"},
     *     summary="Delete DID Block by ID for authorized user",
     *     description="This deletes the block and its child Dids",
     *     operationId="deleteDidblock",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of block to Delete",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->delete('didblock/{id}', 'App\Http\Controllers\Didcontroller@deleteDidblock');

    // List DIDs by block id
    /**
     * @SWG\Get(
     *     path="/telephony/api/didblock/{id}/dids",
     *     tags={"Did"},
     *     summary="List DIDs for Did Block by ID for authorized user",
     *     description="List child DIDs for Did Block by ID",
     *     operationId="listDidbyBlockID",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of block id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->get('didblock/{id}/dids', 'App\Http\Controllers\Didcontroller@listDidbyBlockID');

    // DID App routes
    // $api->post('did', 'App\Http\Controllers\Didcontroller@createDid'); // Individual DID creation not allowed.
        // List DIDs by block id

    /**
     * @SWG\Get(
     *     path="/telephony/api/did/id/{id}",
     *     tags={"Did"},
     *     summary="Get DID by ID for authorized user",
     *     description="",
     *     operationId="getDid",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of did id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->get('did/id/{id}', 'App\Http\Controllers\Didcontroller@getDid');

    /**
     * @SWG\Get(
     *     path="/telephony/api/did/number/{number}",
     *     tags={"Did"},
     *     summary="Get DID by number search for authorized user",
     *     description="",
     *     operationId="getDid",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="number",
     *         in="path",
     *         description="Search for Number",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->get('did/number/{number}', 'App\Http\Controllers\Didcontroller@searchDidNumber');

    /**
     * @SWG\Get(
     *     path="/telephony/api/did/searchbyparent/{parentid}/{column}/{search}",
     *     tags={"Did"},
     *     summary="Search DID by parent ID and column search for authorized user",
     *     description="",
     *     operationId="getDid",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="parentid",
     *         in="path",
     *         description="ID of parent",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="column",
     *         in="path",
     *         description="Column to Search",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="search",
     *         in="path",
     *         description="Search String",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     * )
     **/
    $api->get('did/searchbyparent/{parentid}/{column}/{search}', 'App\Http\Controllers\Didcontroller@searchDidbyParent');

    /**
     * @SWG\Put(
     *     path="/telephony/api/did/{id}",
     *     tags={"Did"},
     *     summary="Update DID by ID for authorized user",
     *     description="This can be huge and need to add pagination",
     *     operationId="listDid",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of did",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         in="formData",
     *         description="Name of New Block",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="status",
     *         in="formData",
     *         description="Available, Reserved",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     * )
     **/
    $api->put('did/{id}', 'App\Http\Controllers\Didcontroller@updateDid');
    // $api->delete('did/{id}', 'App\Http\Controllers\Didcontroller@deleteDid'); // Individual DID deletion Not allowed.

    /**
     * @SWG\Post(
     *     path="/telephony/api/did/searchDidNumbersinArray",
     *     tags={"Did"},
     *     summary="Search DIDs for Numbers in Array",
     *     description="",
     *     operationId="createDidblock",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="delimiter",
     *         in="formData",
     *         description="Number block delimiter - required if using blocks",
     *		   enum={"comma", "tab", "space"},
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="blocks",
     *         in="formData",
     *         description="Blocks to lookup - 1 per line. Space, comma, or Tab delimited",
     *         required=false,
     *         type="array",
     *         @SWG\Items(
     *             type="string",
     *             description="Numbers Array",
     *         ),
     *     ),
     *     @SWG\Parameter(
     *         name="numbers",
     *         in="formData",
     *         description="Numbers to lookup - 1 per line.",
     *         required=false,
     *         type="array",
     *         @SWG\Items(
     *             type="string",
     *             description="Numbers Array",
     *         ),
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->post('did/searchDidNumbersinArray', 'App\Http\Controllers\Didcontroller@searchDidNumbersinArray');

    /**
     * @SWG\Post(
     *     path="/telephony/api/did/searchDidblockNumbersinArray",
     *     tags={"Did"},
     *     summary="Search DID Block Numbers in Array",
     *     description="",
     *     operationId="createDidblock",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="delimiter",
     *         in="formData",
     *         description="Number block delimiter - required if using blocks",
     *		   enum={"comma", "tab", "space"},
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="blocks",
     *         in="formData",
     *         description="Blocks to lookup - 1 per line. Space, comma, or Tab delimited",
     *         required=false,
     *         type="array",
     *         @SWG\Items(
     *             type="string",
     *             description="Numbers Array",
     *         ),
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     **/
    $api->post('did/searchDidblockNumbersinArray', 'App\Http\Controllers\Didcontroller@searchDidblockNumbersinArray');

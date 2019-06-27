<?php

    /********************************
        Site Phone Plan App routes
    ********************************/

    /**
     * @SWG\Post(
     *     path="/telephony/api/phoneplan",
     *     tags={"Site Planning - Phone Plan"},
     *     summary="Create New Phone Plans for Phone Import Job",
     *     description="",
     *     operationId="createPhoneplan",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="site",
     *         in="formData",
     *         description="Parent Site ID Number",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         in="formData",
     *         description="Phone Name - Example 0004DEADBEEF",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="description",
     *         in="formData",
     *         description="description",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="status",
     *         in="formData",
     *         description="",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="system_id",
     *         in="formData",
     *         description="",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="language",
     *         in="formData",
     *         description="Language",
     *		   enum={"English", "French"},
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="json",
     *         in="formData",
     *         description="Unstructure Stuff",
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
    $api->post('phoneplan', 'App\Http\Controllers\SitePlanController@createphoneplan');

    // List phones by block id
    /**
     * @SWG\Get(
     *     path="/telephony/api/phoneplan/site/{id}",
     *     tags={"Site Planning - Phone Plan"},
     *     summary="List phones for Site Plan by ID for authorized user",
     *     description="List child phones for Site Plan by ID",
     *     operationId="listphoneplanbysiteid",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Number of Site",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *
     *     ),
     * )
     **/
    $api->get('/phoneplan/site/{id}', 'App\Http\Controllers\SitePlanController@listphoneplanbysiteid');

    // phone App routes
    // $api->post('phone', 'App\Http\Controllers\SitePlanController@createphone'); // Individual phone creation not allowed.
        // List phones by block id

    /**
     * @SWG\Get(
     *     path="/telephony/api/phoneplan/id/{id}",
     *     tags={"Site Planning - Phone Plan"},
     *     summary="Get Phoneplan by ID",
     *     description="",
     *     operationId="getphoneplanbyid",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of phoneplan id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *
     *     ),
     * )
     **/
    $api->get('phoneplan/id/{id}', 'App\Http\Controllers\SitePlanController@getphoneplan');

    /**
     * @SWG\Get(
     *     path="/telephony/api/phoneplan/id/{id}/phones",
     *     tags={"Site Planning - Phone Plan"},
     *     summary="Get Phone Plan Phones by Plan ID",
     *     description="",
     *     operationId="getphoneplanbyid",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of phoneplan id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *
     *     ),
     * )
     **/
    $api->get('phoneplan/id/{id}/phones', 'App\Http\Controllers\SitePlanController@getphonebyphoneplan');

    /**
     * @SWG\Get(
     *     path="/telephony/api/phoneplan/name/{name}",
     *     tags={"Site Planning - Phone Plan"},
     *     summary="Get phone plan by name",
     *     description="",
     *     operationId="getphoneplanbyname",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="Search Site Plans for Phones with DN Number",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *
     *     ),
     * )
     **/
    $api->get('phoneplan/name/{name}', 'App\Http\Controllers\SitePlanController@getphoneplanbyname');

    /**
     * @SWG\Put(
     *     path="/telephony/api/phoneplan/{id}",
     *     tags={"Site Planning - Phone Plan"},
     *     summary="Update phone by ID for authorized user",
     *     description="This can be huge and need to add pagination",
     *     operationId="listphone",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="Phone Plan ID Number",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="site",
     *         in="formData",
     *         description="Parent Site ID Number",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         in="formData",
     *         description="Phone Name - Example 0004DEADBEEF",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="description",
     *         in="formData",
     *         description="description",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="status",
     *         in="formData",
     *         description="",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="system_id",
     *         in="formData",
     *         description="",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="language",
     *         in="formData",
     *         description="Language",
     *		   enum={"English", "French"},
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="json",
     *         in="formData",
     *         description="Unstructure Stuff",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     * )
     **/
    $api->put('phoneplan/{id}', 'App\Http\Controllers\SitePlanController@updatephoneplan');

    /**
     * @SWG\Delete(
     *     path="/telephony/api/phoneplan/{id}",
     *     tags={"Site Planning - Phone Plan"},
     *     summary="Delete Phone from Site Plan by ID for authorized user",
     *     description="This deletes the phone by ID number",
     *     operationId="deletephone",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of Phone to Delete",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *
     *     ),
     * )
     **/
    $api->delete('phoneplan/{id}', 'App\Http\Controllers\SitePlanController@deletephoneplan');

<?php

    /**
     * @SWG\Get(
     *     path="/telephony/api/cupi/user/search/{alias}",
     *     tags={"Management - UnityConnection"},
     *     summary="Seach for current user by alias",
     *     description="",
     *     operationId="getObjectTypebyName",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="alias",
     *         in="path",
     *         description="Name of Object",
     *         required=true,
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
    $api->get('cupi/user/search/{alias}', 'App\Http\Controllers\Cupicontroller@finduserbyalias');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cupi/user/getLDAPUserbyAlias/{alias}",
     *     tags={"Management - UnityConnection"},
     *     summary="Seach for LDAP user by alias",
     *     description="",
     *     operationId="getObjectTypebyName",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="alias",
     *         in="path",
     *         description="Name of Object",
     *         required=true,
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
    $api->get('cupi/user/getLDAPUserbyAlias/{alias}', 'App\Http\Controllers\Cupicontroller@getLDAPUserbyAlias');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cupi/user/extension/{extension}",
     *     tags={"Management - UnityConnection"},
     *     summary="Seach for current user by extension",
     *     description="",
     *     operationId="getObjectTypebyExension",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="extension",
     *         in="path",
     *         description="Name of Object",
     *         required=true,
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
    $api->get('cupi/user/extension/{extension}', 'App\Http\Controllers\Cupicontroller@findmailboxbyextension');

    /**
     * @SWG\Post(
     *     path="/telephony/api/cupi/user/ldapimport",
     *     tags={"Management - UnityConnection"},
     *     summary="Import User Mailbox from LDAP",
     *     description="",
     *     operationId="importuser",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="username",
     *         in="formData",
     *         description="Sitecode",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="dn",
     *         in="formData",
     *         description="Extension Number for Mailbox",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="template",
     *         in="formData",
     *         description="Extension Number for Mailbox",
     *         required=true,
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
    $api->post('cupi/user/ldapimport', 'App\Http\Controllers\Cupicontroller@importLDAPUser');

    /**
     * @SWG\Post(
     *     path="/telephony/api/cupi/user/create",
     *     tags={"Management - UnityConnection"},
     *     summary="Import User Mailbox from LDAP",
     *     description="",
     *     operationId="importuser",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="username",
     *         in="formData",
     *         description="Sitecode",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="dn",
     *         in="formData",
     *         description="Extension Number for Mailbox",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="template",
     *         in="formData",
     *         description="User Template Name",
     *         required=true,
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
    $api->post('cupi/user/create', 'App\Http\Controllers\Cupicontroller@createuser');

    /**
     * @SWG\Delete(
     *     path="/telephony/api/cupi/user/delete/{username}",
     *     tags={"Management - UnityConnection"},
     *     summary="Delete User",
     *     description="This deletes the user by username",
     *     operationId="deleteuser",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="username",
     *         in="path",
     *         description="username",
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
    $api->delete('/cupi/user/delete/{username}', 'App\Http\Controllers\Cupicontroller@deleteuser');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cupi/usertemplate/{name}",
     *     tags={"Management - UnityConnection"},
     *     summary="Get UM External Service for User Object",
     *     description="",
     *     operationId="getObjectTypebyName",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="User Object ID",
     *         required=true,
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
    $api->get('cupi/usertemplate/{name}', 'App\Http\Controllers\Cupicontroller@getusertemplate');

    /**
     * @SWG\Post(
     *     path="/telephony/api/cupi/usertemplate/create",
     *     tags={"Management - UnityConnection"},
     *     summary="Create User Template",
     *     description="",
     *     operationId="createUserTemplate",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="sitecode",
     *         in="formData",
     *         description="New Template Name",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         in="formData",
     *         description="New Template Name",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="language",
     *         in="formData",
     *         description="Template to Copy",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="timezone",
     *         in="formData",
     *         description="Timezone",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="operator",
     *         in="formData",
     *         description="Timezone",
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
    $api->post('cupi/usertemplate/create', 'App\Http\Controllers\Cupicontroller@createusertemplate');

    /**
     * @SWG\Post(
     *     path="/telephony/api/cupi/usertemplate/site",
     *     tags={"Management - UnityConnection"},
     *     summary="Create Employee and Partner Templates for Site",
     *     description="",
     *     operationId="createUserTemplate",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="sitecode",
     *         in="formData",
     *         description="New Template Name",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="language",
     *         in="formData",
     *         description="Language",
     *         required=true,
     *         enum={"english", "french"},
     *		   type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="timezone",
     *         in="formData",
     *         description="Timezone",
     *         required=true,
     *         enum={"Alaska-12", "Arizona-12", "Central-12", "Eastern-12", "Hawaii-12", "Mountain-12", "Pacific-12"},
     *		   type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="operator",
     *         in="formData",
     *         description="Timezone",
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
    $api->post('cupi/usertemplate/site', 'App\Http\Controllers\Cupicontroller@createusertemplatesforsite');

    /**
     * @SWG\Delete(
     *     path="/telephony/api/cupi/usertemplate/deletebyname/{name}",
     *     tags={"Management - UnityConnection"},
     *     summary="Delete UserTemplate",
     *     description="This deletes the user template by name",
     *     operationId="delete_usertemplate",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="Template Alias",
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
    $api->delete('/cupi/usertemplate/deletebyname/{name}', 'App\Http\Controllers\Cupicontroller@delete_usertemplate');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cupi/usertemplates/listusertemplatesbysite/{sitecode}",
     *     tags={"Management - UnityConnection"},
     *     summary="Get UM External Service for User Object",
     *     description="",
     *     operationId="getObjectTypebyName",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="sitecode",
     *         in="path",
     *         description="Sitecode",
     *         required=true,
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
    $api->get('cupi/usertemplates/listusertemplatesbysite/{sitecode}', 'App\Http\Controllers\Cupicontroller@listusertemplatesbysite');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cupi/usertemplates/listusertemplates",
     *     tags={"Management - UnityConnection"},
     *     summary="List User Templates",
     *     description="",
     *     operationId="getObjectTypebyName",
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
    $api->get('cupi/usertemplates/listusertemplates', 'App\Http\Controllers\Cupicontroller@listusertemplates');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cupi/usertemplates/list_call_handlers",
     *     tags={"Management - UnityConnection"},
     *     summary="List Call Handlers",
     *     description="",
     *     operationId="list_call_handlers",
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
    $api->get('cupi/usertemplates/list_call_handlers', 'App\Http\Controllers\Cupicontroller@list_call_handlers');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cupi/callhandler/extension/{extension}",
     *     tags={"Management - UnityConnection"},
     *     summary="Seach for current callhandler by extension",
     *     description="",
     *     operationId="getObjectTypebyExension",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="extension",
     *         in="path",
     *         description="Name of Object",
     *         required=true,
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
    $api->get('cupi/callhandler/extension/{extension}', 'App\Http\Controllers\Cupicontroller@get_callhandler_by_extension');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cupi/usertemplates/names",
     *     tags={"Management - UnityConnection"},
     *     summary="List User Template Names",
     *     description="",
     *     operationId="getObjectTypebyName",
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
    $api->get('cupi/usertemplates/names', 'App\Http\Controllers\Cupicontroller@listusertemplatenames');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cupi/timezones",
     *     tags={"Management - UnityConnection"},
     *     summary="List Timezones",
     *     description="",
     *     operationId="ListTimeZones",
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
    $api->get('cupi/timezones', 'App\Http\Controllers\Cupicontroller@listtimezones');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cupi/listexternalservices",
     *     tags={"Management - UnityConnection"},
     *     summary="List User Template Names",
     *     description="",
     *     operationId="getObjectTypebyName",
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
    $api->get('cupi/listexternalservices', 'App\Http\Controllers\Cupicontroller@listexternalservices');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cupi/user/getuserunifiedmessaging/{objectid}",
     *     tags={"Management - UnityConnection"},
     *     summary="Get UM External Service for User Object",
     *     description="",
     *     operationId="getObjectTypebyName",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="objectid",
     *         in="path",
     *         description="User Object ID",
     *         required=true,
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
    $api->get('cupi/user/getuserunifiedmessaging/{objectid}', 'App\Http\Controllers\Cupicontroller@getuserunifiedmessaging');

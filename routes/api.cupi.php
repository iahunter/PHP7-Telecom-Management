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

     *     ),
     * )
     **/
    $api->delete('/cupi/user/delete/{username}', 'App\Http\Controllers\Cupicontroller@deleteuser');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cupi/templates/listusertemplates",
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
    $api->get('cupi/templates/listusertemplates', 'App\Http\Controllers\Cupicontroller@listusertemplates');

    /**
     * @SWG\Get(
     *     path="/telephony/api/cupi/templates/listusertemplatesnames",
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
    $api->get('cupi/templates/listusertemplatesnames', 'App\Http\Controllers\Cupicontroller@listusertemplatenames');

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

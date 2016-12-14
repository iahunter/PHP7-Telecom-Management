<?php

		/**
		 * @SWG\Get(
		 *     path="/telephony/api/ldap/users",
		 *     tags={"Management - LDAP"},
		 *     summary="List LDAP Users",
		 *     description="",
		 *     operationId="",
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
		//$api->get('ldap/users', 'App\Http\Controllers\Ldap@listusers');
		
		/**
		 * @SWG\Get(
		 *     path="/telephony/api/ldap/user/get/{username}",
		 *     tags={"Management - LDAP"},
		 *     summary="Get User IPPhone in LDAP",
		 *     description="",
		 *     operationId="",
		 *     consumes={"application/json"},
		 *     produces={"application/json"},
		 *     @SWG\Parameter(
		 *         name="username",
		 *         in="path",
		 *         description="samAccountName",
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
		$api->get('ldap/user/get/{username}', 'App\Http\Controllers\Ldap@get_user');
		
		/**
		 * @SWG\Put(
		 *     path="/telephony/api/ldap/user/update/ipphone",
		 *     tags={"Management - LDAP"},
		 *     summary="Change IP Phone in LDAP",
		 *     description="This updates the IP Phone Field in Active Directory so that CUCM imports the user into its database during the LDAP Sync process.",
		 *     operationId="",
		 *     consumes={"application/json"},
		 *     produces={"application/json"},
		 *     @SWG\Parameter(
		 *         name="username",
		 *         in="formData",
		 *         description="samAccountName",
		 *         required=true,
		 *         type="string"
		 *     ),
		 *     @SWG\Parameter(
		 *         name="ipphone",
		 *         in="formData",
		 *         description="Directory Number",
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
		$api->put('ldap/user/update/ipphone', 'App\Http\Controllers\Ldap@user_update_ipphone');
		



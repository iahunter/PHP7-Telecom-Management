<?php

namespace App\Http\Controllers;

use App\Cupi;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class Cupicontroller extends Controller
{
    public function finduserbyalias(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cupi::class)) {
            abort(401, 'You are not authorized');
        }

        $alias = $request->alias;
        //$alias = "travis.riesenberg";
        return Cupi::finduserbyalias($alias);
    }

    public function getLDAPUserbyAlias(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cupi::class)) {
            abort(401, 'You are not authorized');
        }

        $alias = $request->alias;
        //$alias = "travis.riesenberg";
        return Cupi::getLDAPUserbyAlias($alias);
    }

    public function findmailboxbyextension(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cupi::class)) {
            abort(401, 'You are not authorized');
        }

        $extension = $request->extension;
        //$alias = "travis.riesenberg";
        return Cupi::findmailboxbyextension($extension);
    }

    public function importLDAPUser(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', Cupi::class)) {
            abort(401, 'You are not authorized');
        }

        //return $request;
        if (isset($request->username) && $request->username) {
            $username = $request->username;
        }

        if (isset($request->dn) && $request->dn) {
            $dn = $request->dn;
        }

        if (isset($request->template) && $request->template) {
            $template = $request->template;
        }

        if (isset($request->override) && $request->override) {
            $override = $request->override;
        } else {
            $override = true;
        }

        //$alias = "travis.riesenberg";
        return Cupi::importLDAPUser($username, $dn, $template, $override);
    }

    public function createuser(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', Cupi::class)) {
            abort(401, 'You are not authorized');
        }

        //return $request;
        if (isset($request->username) && $request->username) {
            $username = $request->username;
        }

        if (isset($request->dn) && $request->dn) {
            $dn = $request->dn;
        }

        if (isset($request->template) && $request->template) {
            $template = $request->template;
        }

        //$alias = "travis.riesenberg";
        return Cupi::createuser($username, $dn, $template);
    }

    public function updateuserdn(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('update', Cupi::class)) {
            abort(401, 'You are not authorized');
        }

        //return $request;
        if (isset($request->username) && $request->username) {
            $username = $request->username;
        }

        if (isset($request->dn) && $request->dn) {
            $dn = $request->dn;
        }

        //$alias = "travis.riesenberg";
        return Cupi::createuser($username, $dn);
    }

    public function deleteuser(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('delete', Cupi::class)) {
            abort(401, 'You are not authorized');
        }

        if (isset($request->username) && $request->username) {
            $username = $request->username;
        }

        return Cupi::deleteuser($username);
    }

    public function listusertemplates(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cupi::class)) {
            abort(401, 'You are not authorized');
        }

        return Cupi::listusertemplates();
    }

    public function listusertemplatenames(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cupi::class)) {
            abort(401, 'You are not authorized');
        }

        return Cupi::listusertemplatenames();
    }

    public function listexternalservices(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cupi::class)) {
            abort(401, 'You are not authorized');
        }

        return Cupi::listexternalservices();
    }

    public function getuserunifiedmessaging(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cupi::class)) {
            abort(401, 'You are not authorized');
        }

        return Cupi::getuserexternalservice($request->objectid);
    }

    public function listtimezones(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cupi::class)) {
            abort(401, 'You are not authorized');
        }

        return Cupi::listtimezones();
    }

    public function getusertemplate(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cupi::class)) {
            abort(401, 'You are not authorized');
        }

        if (isset($request->name) && $request->name) {
            $name = $request->name;
        }

        $newtemplate = Cupi::getusertemplate($name);

        return $newtemplate;
    }

    public function listusertemplatesbysite(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cupi::class)) {
            abort(401, 'You are not authorized');
        }

        if (isset($request->sitecode) && $request->sitecode) {
            $sitecode = $request->sitecode;
        }

        $templates = Cupi::getusertemplatebysite($sitecode);

        return $templates;
    }

    public function createusertemplate(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', Cupi::class)) {
            abort(401, 'You are not authorized');
        }

        /* Example
        {
            "Alias":"TRAVIS01_Kiewit_User",
            "DisplayName":"TRAVIS01_Kiewit_User",
            "City": "Omaha",
            "State": "NE",
            "Country": "US",
            "PostalCode": "68164",
            "Department": "",
            "Manager": "",
            "Building": "",
            "Address": ""
        }
        */

        //return $request;

        if (isset($request->sitecode) && $request->sitecode) {
            $sitecode = $request->sitecode;
        }

        // This needs work!!!!
        if (isset($request->name) && $request->name) {
            $template['Alias'] = $request->name;
            $template['DisplayName'] = $request->name;
        }

        // Timezone must use some weird index number. Use listtimezones to search or match id.
        if (isset($request->timezone) && $request->timezone) {
            $template['Timezone'] = $request->timezone;
        }

        if (isset($request->copytemplate) && $request->copytemplate) {
            $copytemplate = $request->copytemplate;
        }

        if (isset($request->operator) && $request->operator) {
            $operator = $request->operator;
        } else {
            $operator = '';
        }

        $newtemplate = Cupi::createusertemplate($sitecode, $template, $copytemplate, $operator);

        return $newtemplate;
    }

    public function update_usertemplate_operator(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('update', Cupi::class)) {
            abort(401, 'You are not authorized');
        }

        if (isset($request->objectid) && $request->objectid) {
            $objectid = $request->objectid;
        }

        if (isset($request->sitecode) && $request->sitecode) {
            $sitecode = $request->sitecode;
        }

        if (isset($request->operator) && $request->operator) {
            $operator = $request->operator;
        }

        return Cupi::update_usertemplate_operator($objectid, $sitecode, $operator);
    }

    public function delete_usertemplate(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('delete', Cupi::class)) {
            abort(401, 'You are not authorized');
        }

        if (isset($request->name) && $request->name) {
            $name = $request->name;
        }

        $return = Cupi::delete_usertemplate($name);
    }
}

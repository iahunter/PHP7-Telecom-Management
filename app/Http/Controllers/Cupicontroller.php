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
}

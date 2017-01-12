<?php

namespace App\Http\Controllers;

use DB;
use App\Site;
use App\Phone;
use Illuminate\Http\Request;
// Include the JWT Facades shortcut
use Tymon\JWTAuth\Facades\JWTAuth;

//use Dingo\Api\Routing\Helpers;

class SitePlanController extends Controller
{
    //use Helpers;
    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');
    }

    public function listSites()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $sites = Site::all();

        $show = [];
        foreach ($sites as $site) {
            if ($user->can('read', $site)) {
                $show[] = $site;
            }
        }
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'sites'      => $show,
                    ];

        return response()->json($response);
    }

    public function getsite(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $site = Site::find($id);

        if (! $user->can('read', $site)) {
            abort(401, 'You are not authorized to view site '.$id);
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'request'        => $request->all(),
                    'site'       => $site,
                    ];

        return response()->json($response);
    }

    public function createsite(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('create', Site::class)) {
            abort(401, 'You are not authorized to create new Phone blocks');
        }

        $site = Site::create($request->all());

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'request'        => $request->all(),
                    'site'       => $site,
                    ];

        return response()->json($response);
    }

    public function updatesite(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Find record by id
        $site = Site::find($id);

        // Check Role of user
        if (! $user->can('update', $site)) {
            abort(401, 'You are not authorized to view site '.$id);
        }

        $site->fill($request->all());
        $site->save();

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'request'        => $request->all(),
                    'site'       => $site,
                    ];

        return response()->json($response);
    }

    public function deletesite(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('delete', Site::class)) {
            abort(401, 'You are not authorized to delete Phone block id '.$id);
        }

        $site = Site::find($id);                                        // Find the block in the database by id
        $site->delete();                                                            // Delete the Phone block.
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => 'Phone Block '.$id.' successfully deleted',
                    'deleted_at'     => $site->deleted_at, ];

        return response()->json($response);
    }

	public function createPhone(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('create', Phone::class)) {
            abort(401, 'You are not authorized to create new Phone blocks');
        }

        $phone = Phone::create($request->all());

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'request'        => $request->all(),
                    'phone'       => $phone,
                    ];

        return response()->json($response);
    }

    public function listphonebySiteID(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->can('read', Phone::class)) {
            $Phones = \App\Phone::where('parent', $id)->get();
        }
        //dd($Phones);
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'phones'           => $Phones,
                    ];

        return response()->json($response);
    }

    public function getPhone(Request $request, $Phone_id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $Phone = Phone::find($Phone_id);
        if (! $user->can('read', $Phone)) {
            abort(401, 'You are not authorized to view site '.$Phone_id);
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'request'        => $request->all(),
                    'phone'            => $Phone,
                    ];

        return response()->json($response);
    }

    public function searchPhoneNumber(Request $request, $number_search)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user->can('read', Phone::class)) {
            abort(401, 'You are not authorized to view site '.$Phone);
        }

        // Search for Phone by numberCheck if there are any matches.
        if (! Phone::where([['dn', 'like', $number_search.'%']])->count()) {
            abort(404, 'No dn found matching search: '.$number_search);
        }

        // Search for numbers like search.
        $Phones = Phone::where([['dn', 'like', $number_search.'%']])->get();

        //return "HERE ".$Phone;

        $response = [
                    'status_code'     => 200,
                    'success'         => true,
                    'message'         => '',
                    'request'         => $request->all(),
                    'phones'            => $Phones,
                    ];

        return response()->json($response);
    }

    public function searchPhonebyParent(Request $request, $parentid, $column, $search)
    {
        // ********NEW SEARCH***********
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user->can('read', Phone::class)) {
            abort(401, 'You are not authorized to view site '.$Phone);
        }

        // Search for Phone by numberCheck if there are any matches.
        if (! Phone::where([['parent', '=', $parentid], [$column, 'like', $search.'%']])->count()) {
            abort(404, 'No number found matching search: '.$search);
        }

        // Search for numbers like search.
        $Phones = Phone::where([['parent', '=', $parentid], [$column, 'like', $search.'%']])->get();

        //return "HERE ".$Phone;

        $response = [
                    'status_code'     => 200,
                    'success'         => true,
                    'message'         => '',
                    'request'         => $request->all(),
                    'phones'            => $Phones,
                    ];

        return response()->json($response);
    }

    public function updatePhone(Request $request, $Phone_id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Find record by id
        $Phone = Phone::find($Phone_id);

        // Check Role of user
        if (! $user->can('update', $Phone)) {
            abort(401, 'You are not authorized to view site '.$Phone_id);
        }

        $Phone->fill($request->all());
        $Phone->save();

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'request'        => $request->all(),
                    'phone'            => $Phone,
                    ];

        return response()->json($response);
    }


    public function deletePhone(Request $request, $Phone_id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('delete', Phone::class)) {
            abort(401, 'You are not authorized to delete Phone id '.$account_id);
        }

        $Phone = Phone::find($Phone_id);                                        // Find the block in the database by id
        $Phone->delete();                                                            // Delete the Phone block.
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => 'Phone '.$Phone_id.' successfully deleted',
                    'deleted_at'     => $Phone->deleted_at, 
					'phone'     	 => $Phone, 
					];
					

        return response()->json($response);

    }
}

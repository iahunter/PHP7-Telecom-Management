<?php

namespace App\Http\Controllers;

use App\Didblock;
use App\Phone;
use App\Phoneplan;
use App\Site;
use DB;
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

        // Check Role of user
        if (! $user->can('read', Site::class)) {
            abort(401, 'You are not authorized');
        }

        $sites = Site::orderBy('sitecode')->get();

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
                    'sites'          => $show,
                    ];

        return response()->json($response);
    }

    public function getDidblockUtilization()
    {
        $stats = DB::table('did_block')
            ->leftJoin('did', 'did_block.id', '=', 'did.parent')
            ->select('did_block.id', 'did_block.name', 'did.status', DB::raw('count(did.id) as statuscount'))
            ->groupBy('did_block.id')
            ->groupBy('did.status')
            ->get();

        $statsarray = [];

        foreach ($stats as $stat) {
            if (! isset($statsarray[$stat->id])) {
                $statsarray[$stat->id] = [
                    'available'    => 0,
                    'inuse'        => 0,
                    'reserved'     => 0,
                ];
            }
            $statsarray[$stat->id][$stat->status] = $stat->statuscount;
        }

        return $statsarray;
    }

    public function getsite(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', Site::class)) {
            abort(401, 'You are not authorized');
        }

        $site = Site::find($id);

        $didblocks = [];
        //return $site;

        if ((isset($site['didblocks'])) && is_array($site['didblocks'])) {
            foreach ($site['didblocks'] as $didblock) {
                //print $didblock;

                $didblock = Didblock::find($didblock);

                if ($didblock) {
                    // Get stats
                    $stats = $this->getDidblockUtilization();

                    $didblock->stats = $stats[$didblock->id];
                    $didblocks[] = $didblock;
                }
            }
            $site['didblocksdetails'] = $didblocks;
        }

        if (! $user->can('read', $site)) {
            abort(401, 'You are not authorized to view site '.$id);
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    //'request'        => $request->all(),
                    'result'           => $site,
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
        $sitecode = strtoupper($request->sitecode);

        // Change sitecode to uppercase
        $request->merge(['sitecode' => $sitecode]);

        $request->merge(['created_by' => $user->username]);
        $site = Site::create($request->all());

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    //'request'        => $request->all(),
                    'site'           => $site,
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

        $request->merge(['updated_by' => $user->username]);
        $site->fill($request->all());
        $site->save();

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'request'        => $request->all(),
                    'site'           => $site,
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

        $request->merge(['updated_by' => $user->username]);
        $site->fill($request->all());
        $site->save();

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

        $request->merge(['created_by' => $user->username]);
        $phone = Phone::create($request->all());

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    //'request'        => $request->all(),
                    'result'          => $phone,
                    ];

        return response()->json($response);
    }

    public function getphone(Request $request, $Phone_id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $Phone = Phone::find($Phone_id);
        if (! $user->can('read', $Phone)) {
            abort(401, 'You are not authorized to view site '.$Phone_id);
        }

        $response = [
                    'status_code'      => 200,
                    'success'          => true,
                    'message'          => '',
                    //'request'          => $request->all(),
                    'result'            => $Phone,
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
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    //'request'           => $request->all(),
                    'result'            => $Phones,
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

        $request->merge(['updated_by' => $user->username]);
        $Phone->fill($request->all());
        $Phone->save();

        $response = [
                    'status_code'      => 200,
                    'success'          => true,
                    'message'          => '',
                    //'request'          => $request->all(),
                    'result'            => $Phone,
                    ];

        return response()->json($response);
    }

    public function deletePhone(Request $request, $Phone_id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $Phone = Phone::find($Phone_id);   // Find the block in the database by id

        // Check Role of user
        if (! $user->can('delete', Phone::class)) {
            abort(401, 'You are not authorized to delete Phone id '.$account_id);
        }

        $request->merge(['updated_by' => $user->username]);
        $Phone->fill($request->all());
        $Phone->save();

        $Phone->delete();                                                            // Delete the Phone block.
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => 'Phone '.$Phone_id.' successfully deleted',
                    'phone'          => $Phone,
                    'deleted_at'     => $Phone->deleted_at,
                    ];

        return response()->json($response);
    }

    /**
     * Phone Plans.
     **/
    public function listphoneplan()
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', Phoneplan::class)) {
            abort(401, 'You are not authorized to view Phone Plan');
        }

        $phoneplans = Phoneplan::all();

        $show = [];
        foreach ($phoneplans as $phoneplan) {
            if ($user->can('read', $phoneplan)) {
                $show[] = $phoneplan;
            }
        }
        $response = [
                    'status_code'     => 200,
                    'success'         => true,
                    'message'         => '',
                    'result'          => $show,
                    ];

        return response()->json($response);
    }

    public function listphoneplanbysiteid(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', Phoneplan::class)) {
            abort(401, 'You are not authorized to view Phone Plan');
        }

        // Search for Phone by numberCheck if there are any matches.
        /*
        if (! Phoneplan::where('site', '=', $id)->count()) {
            abort(404, 'No plan found matching site: '.$id);
        }
        */

        // Search for numbers like search.
        $phoneplan = Phoneplan::where('site', '=', $id)->get();

        $response = [
            'status_code'      => 200,
            'success'          => true,
            'message'          => '',
            //'request'        => $request->all(),
            'result'           => $phoneplan,
            ];

        return response()->json($response);
    }

    public function listphonesbysiteid(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', Phoneplan::class)) {
            abort(401, 'You are not authorized to view Phone Plan');
        }

        /*
        // Search for Phone by numberCheck if there are any matches.
        if (! Phone::where('site', '=', $id)->count()) {
            abort(404, 'No number found matching search: '.$search);
        }
        */

        // Search for numbers like search.
        $phones = Phone::where('site', '=', $id)->get();

        $response = [
            'status_code'      => 200,
            'success'          => true,
            'message'          => '',
            //'request'        => $request->all(),
            'result'           => $phones,
            ];

        return response()->json($response);
    }

    public function getphoneplanbyname(Request $request, $name)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', Phoneplan::class)) {
            abort(401, 'You are not authorized to view Phone Plan');
        }

        /*
        // Search for Phone by numberCheck if there are any matches.
        if (! Phoneplan::where('name', '=', $name)->count()) {
            abort(404, 'No number found matching search: '.$search);
        }
        */

        // Search for numbers like search.
        $phoneplan = Phoneplan::where('name', '=', $name)->get();

        $response = [
            'status_code'      => 200,
            'success'          => true,
            'message'          => '',
            //'request'        => $request->all(),
            'result'           => $phoneplan,
            ];

        return response()->json($response);
    }

    public function getphoneplan(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $phoneplan = Phoneplan::find($id);

        if (! $user->can('read', $phoneplan)) {
            abort(401, 'You are not authorized to view site '.$id);
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    //'request'        => $request->all(),
                    'result'           => $phoneplan,
                    ];

        return response()->json($response);
    }

    public function getphonebyphoneplan(Request $request, $id)
    {
        // ********NEW SEARCH***********
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user->can('read', Phone::class)) {
            abort(401, 'You are not authorized to view phone plan '.$id);
        }

        /*
        // Search for Phone by numberCheck if there are any matches.
        if (! Phone::where('phoneplan', '=', $id)->count()) {
            abort(404, 'No phones found matching Phoneplan: '.$id);
        }
        */

        // Search for numbers like search.
        $Phones = Phone::where('phoneplan', '=', $id)->get();

        //return "HERE ".$Phone;

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    //'request'           => $request->all(),
                    'result'            => $Phones,
                    ];

        return response()->json($response);
    }

    public function createphoneplan(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('create', Phoneplan::class)) {
            abort(401, 'You are not authorized to create new Phone blocks');
        }

        $request->merge(['created_by' => $user->username]);
        $phoneplan = Phoneplan::create($request->all());

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    //'request'        => $request->all(),
                    'site'           => $phoneplan,
                    ];

        return response()->json($response);
    }

    public function updatephoneplan(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('update', Phoneplan::class)) {
            abort(401, 'You are not authorized to create new Phone blocks');
        }

        // Find record by id
        $phoneplan = Phoneplan::find($id);

        $request->merge(['updated_by' => $user->username]);
        $phoneplan->fill($request->all());
        $phoneplan->save();

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    //'request'        => $request->all(),
                    'result'           => $phoneplan,
                    ];

        return response()->json($response);
    }

    public function deletephoneplan(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('delete', Site::class)) {
            abort(401, 'You are not authorized to delete Phone block id '.$id);
        }

        $phoneplan = Phoneplan::find($id);                                        // Find the block in the database by id

        $request->merge(['updated_by' => $user->username]);
        $phoneplan->fill($request->all());
        $phoneplan->save();

        $phoneplan->delete();                                                            // Delete the Phone block.

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    //'request'        => $request->all(),
                    'result'         => 'Phone Block '.$id.' successfully deleted',
                    'deleted_at'     => $phoneplan->deleted_at,
                    ];

        return response()->json($response);
    }
}

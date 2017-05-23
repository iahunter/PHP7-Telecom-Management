<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\TelecomInfrastructure;
// Include the JWT Facades shortcut
use Tymon\JWTAuth\Facades\JWTAuth;

class TelecomInfrastructureController extends Controller
{
    public function listDevices()
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user->can('read', TelecomInfrastructure::class)) {
            abort(401, 'You are not authorized');
        }

        $device = TelecomInfrastructure::orderBy('model', 'role')->get();

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'result'         => $device,
                    ];

        return response()->json($response);
    }

    public function getDevice(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $device = TelecomInfrastructure::find($id);

        if (! $user->can('read', $device)) {
            abort(401, 'You are not authorized');
        }

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'request'           => $request->all(),
                    'result'            => $device,
                    ];

        return response()->json($response);
    }

    public function createDevice(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('create', TelecomInfrastructure::class)) {
            abort(401, 'You are not authorized');
        }

        $request->merge(['created_by' => $user->username]);

        $device = TelecomInfrastructure::create($request->all());

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'request'        => $request->all(),
                    'result'         => $device,
                    ];

        return response()->json($response);
    }

    public function updateDevice(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Find record by id
        $device = TelecomInfrastructure::find($id);

        // Check Role of user
        if (! $user->can('update', $device)) {
            abort(401, 'You are not authorized');
        }

        $request->merge(['updated_by' => $user->username]);

        $device->fill($request->all());
        $device->save();

        $response = [
                    'status_code'      => 200,
                    'success'          => true,
                    'message'          => '',
                    'request'          => $request->all(),
                    'result'           => $device,
                    ];

        return response()->json($response);
    }

    public function deleteDevice(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('delete', TelecomInfrastructure::class)) {
            abort(401, 'You are not authorized');
        }

        $device = TelecomInfrastructure::find($id);

        $request->merge(['deleted_by' => $user->username]);

        $device->fill($request->all());
        $device->save();

        // Find the block in the database by id
        $device->delete();                                                            // Delete the did block.
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => 'Device '.$id.' successfully deleted',
                    'deleted_at'     => $device->deleted_at, ];

        return response()->json($response);
    }
}

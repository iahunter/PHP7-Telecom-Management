<?php

namespace App\Http\Controllers;

use App\Reports;
use App\TelecomInfrastructure;
// Include the JWT Facades shortcut
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReportsController extends Controller
{
    /* ********** This needs work!!! ************/

    public function listReportTypes()
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user->can('read', TelecomInfrastructure::class)) {
            abort(401, 'You are not authorized');
        }

        $reports = Reports::where('category', 'network')->where('type', 'vpn_report')->get();

        $response = [
            'status_code'    => 200,
            'success'        => true,
            'message'        => '',
            'result'         => $reports,
        ];

        return response()->json($response);
    }

    public function getReport(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $device = Reports::find($id);

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

    public function createReport(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('create', TelecomInfrastructure::class)) {
            abort(401, 'You are not authorized');
        }

        $request->merge(['created_by' => $user->username]);

        $device = Reports::create($request->all());

        $response = [
            'status_code'    => 200,
            'success'        => true,
            'message'        => '',
            'request'        => $request->all(),
            'result'         => $device,
        ];

        return response()->json($response);
    }

    public function updateReport(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Find record by id
        $device = Reports::find($id);

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

    public function deleteReport(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('delete', TelecomInfrastructure::class)) {
            abort(401, 'You are not authorized');
        }

        $device = Reports::find($id);

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

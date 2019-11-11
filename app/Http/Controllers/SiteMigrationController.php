<?php

namespace App\Http\Controllers;

use App\SiteMigration;
use DB;
use Illuminate\Http\Request;
// Include the JWT Facades shortcut
use Tymon\JWTAuth\Facades\JWTAuth;

class SiteMigrationController extends Controller
{
    //
    public function list_site_migrations()
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', SiteMigration::class)) {
            abort(401, 'You are not authorized');
        }

        $sites = SiteMigration::orderBy('sitecode')->get();

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

    public function get_site_migration(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', SiteMigration::class)) {
            abort(401, 'You are not authorized');
        }

        $migration = SiteMigration::find($id);

        if (! $user->can('read', $migration)) {
            abort(401, 'You are not authorized to view ID: '.$id);
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'request'        => $request->all(),
                    'result'         => $migration,
                    ];

        return response()->json($response);
    }

    public function get_site_migration_by_sitecode(Request $request, $sitecode)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', SiteMigration::class)) {
            abort(401, 'You are not authorized');
        }

        $migrations = SiteMigration::where('sitecode', $sitecode)->get();

        $migrations_array = [];
        foreach ($migrations as $migration) {
            // Change Site type based on site design user chooses. This will determine the site type.
            if ($migration['trunking'] == 'sip' && $migration['e911'] == '911enable') {
                $migration['type'] = 1;
            } elseif ($migration['trunking'] == 'local' && $migration['e911'] == '911enable') {
                $migration['type'] = 2;
            } elseif ($migration['trunking'] == 'sip' && $migration['e911'] == 'local') {
                $migration['type'] = 3;
            } elseif ($migration['trunking'] == 'local' && $migration['e911'] == 'local') {
                $migration['type'] = 4;
            }
            $migrations_array[] = $migration;
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'request'        => $request->all(),
                    'result'         => $migrations_array,
                    ];

        return response()->json($response);
    }

    public function create_site_migration(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('create', SiteMigration::class)) {
            abort(401, 'You are not authorized to create new Phone blocks');
        }
        $sitecode = strtoupper($request->sitecode);

        // Change sitecode to uppercase
        $request->merge(['sitecode' => $sitecode]);

        $request->merge(['created_by' => $user->username]);
        $result = SiteMigration::create($request->all());

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'request'        => $request->all(),
                    'result'         => $result,
                    ];

        return response()->json($response);
    }

    public function update_site_migration(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Find record by id
        $site = SiteMigration::find($id);

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

    public function delete_site_migration(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('delete', SiteMigration::class)) {
            abort(401, 'You are not authorized to delete Phone block id '.$id);
        }

        $site = SiteMigration::find($id);                                        // Find the block in the database by id

        $request->merge(['updated_by' => $user->username]);
        $site->fill($request->all());
        $site->save();

        $site->delete();                                                            // Delete the Phone block.
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => 'Site Migration '.$id.' successfully deleted',
                    'deleted_at'     => $site->deleted_at, ];

        return response()->json($response);
    }
}

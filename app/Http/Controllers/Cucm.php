<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Include the JWT Facades shortcut
use Tymon\JWTAuth\Facades\JWTAuth;

class Cucm extends Controller
{
    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');

        // Construct new cucm object
        $this->cucm = new \CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );
    }

    // Variable to return to user
    public $results;

    public function start_ldap_sync()
    {
        try {
            $ldapsync = $this->cucm->do_ldap_sync(env('CALLMANAGER_LDAP_NAME'), 'true');

            echo $ldapsync->return.PHP_EOL;
        } catch (\Exception $e) {
            echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
        }
    }

    public function stop_ldap_sync()
    {
        try {
            $ldapsync = $this->cucm->do_ldap_sync(env('CALLMANAGER_LDAP_NAME'), 'false');

            echo $ldapsync->return.PHP_EOL;
        } catch (\Exception $e) {
            echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
        }
    }

    // CUCM Add Wrapper
    public function wrap_add_object($DATA, $TYPE)
    {
        // Get the name to reference the object.
        if (isset($DATA['name'])) {
            $OBJECT = $DATA['name'];
        } elseif (isset($DATA['pattern'])) {
            $OBJECT = $DATA['pattern'];
        } else {
            $OBJECT = $TYPE;
        }
        try {
            $REPLY = $this->cucm->add_object_type_by_assoc($DATA, $TYPE);
            $this->results[$TYPE][] = "{$TYPE} CREATED: {$OBJECT} - {$REPLY}";
			return $REPLY;
        } catch (\Exception $E) {
            $EXCEPTION = "Exception adding object type: {$TYPE}".
                  "{$E->getMessage()}".
                  "Stack trace:\n".
                  "{$E->getTraceAsString()}".
                  "Data sent:\n";
            $DATA['exception'] = $EXCEPTION;
            $this->results[$TYPE] = $DATA;
        }
		
    }

    public function listCssDetails(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        try {
            $list = $this->cucm->get_object_type_by_site('%', 'Css');

            if (! count($list)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }

        $CSS_LIST = [];
        foreach ($list as $key => $value) {
            $UUID = $key;

            try {
                $css = $this->cucm->get_object_type_by_uuid($UUID, 'Css');

                if (! count($css)) {
                    throw new \Exception('Indexed results from call mangler is empty');
                }
            } catch (\Exception $e) {
                echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
                dd($e->getTrace());
            }

            $CSS_LIST[] = $css;
            //$CSS_LIST[] = ;
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $CSS_LIST,
                    ];

        return response()->json($response);
    }

    public function listCssDetailsbyName(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $name = $request->name;

        try {
            $css = $this->cucm->get_object_type_by_name($name, 'Css');

            if (! count($css)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $css,
                    ];

        return response()->json($response);
    }

    public function listRoutePatternsByPartition(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        try {
            $result = $this->cucm->get_object_type_by_site($request->routePartitionName, 'RoutePattern');

            if (! count($result)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $result,
                    ];

        return response()->json($response);
    }

    public function getObjectTypebyName(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        try {
            $result = $this->cucm->get_object_type_by_name($request->name, $request->type);

            if (! count($result)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $result,
                    ];

        return response()->json($response);
    }
}

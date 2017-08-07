<?php

namespace App\Http\Controllers;

// Add Dummy CUCM class for permissions use for now.
use App\Cucmclass;
use Illuminate\Http\Request;
// Include the JWT Facades shortcut
use Tymon\JWTAuth\Facades\JWTAuth;

class Cucmphone extends Cucm
{
    public $phones;

    public function uploadPhones(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        // Open CSV and Create a new DID Block for each row.
        $filename = $request->phones;
            //return $filename;
            if (! file_exists($filename) || ! is_readable($filename)) {
                return 'Something is jacked with the file';
            }

        $delimiter = ',';
        $header = null;
        $data = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (! $header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        print_r($data);
    }

    public function phones_string_to_array($INPUT)
    {

        //print_r($INPUT);
        $INPUT = explode(PHP_EOL, $INPUT);
        //print_r($INPUT);

        $PHONES = [];
        foreach ($INPUT as $LINE) {
            if ($LINE == '') {
                unset($LINE);
                continue;
            }
            //$LINE = explode("\t",$LINE);
            //print_r($LINE);
            $PHONE = array_combine(
                                // And map these keys to each value extracted
                                [
                                    'firstname',    'lastname',    'username',        'name',        'device',
                                    'dn',    'language',    'defaultpass',    'voicemail',    'notes',
                                ], explode("\t", $LINE)
                            );
            $PHONES[] = $PHONE;
        }

        return $PHONES;
    }

    public function pastePhones(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        $INPUT = $request->phones;

        $INPUT = explode(',', $INPUT);

        $PHONES = [];
        foreach ($INPUT as $LINE) {
            //$LINE = explode("\t",$LINE);
            //print_r($LINE);
            $PHONE = array_combine(
                                // And map these keys to each value extracted
                                [
                                    'firstname',    'lastname',    'username',       'name',        'device',
                                    'dn',    'language',    'defaultpass',    'voicemail',    'notes',
                                ], explode("\t", $LINE)
                            );
            $PHONE['site'] = $request->sitecode;
            $PHONE['extlength'] = $request->extlength;

            $PHONES[] = $PHONE;
        }

        return $PHONES;
    }

    public function getPhone(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        $name = $request->name;
        $phone = '';
        try {
            $phone = $this->cucm->get_phone_by_name($name);

            if (! count($phone)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            $exception = 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            //dd($e->getTrace());
        }

        if ($phone) {
            // Append Line Details to the phone.

            $phone['line_details'] = $this->cucm->get_lines_details_by_phone_name($name);
        } else {
            $phone = '';
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $phone,
                    ];

        return response()->json($response);
    }

    public function deletePhonebyName($NAME)
    {
        // Try to remove device from CUCM
        try {
            $RESULT = $this->cucm->get_phone_by_name($NAME);
            $TYPE = 'Phone';
            if (is_array($RESULT) && ! empty($RESULT)) {
                $UUID = $RESULT['uuid'];
                $RETURN['old'] = $RESULT;
                $RETURN['deleted_uuid'] = $this->cucm->delete_object_type_by_uuid($UUID, $TYPE);

                // Create log entry

                $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $RETURN,
                    ];
            }
        } catch (\Exception $E) {
            $message = "{$NAME} Does not exist in CUCM Database.".
            "{$E->getMessage()}";

            $response = [
                    'status_code'    => 200,
                    'success'        => false,
                    'message'        => $message,
                    'response'       => '',
                    ];
        }

        return $response;
    }

    public function updatePhone(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('update', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }
        //return $request;

        $TYPE = 'Phone';
        $DATA = $request->phone;

        if (isset($DATA['name'])) {
            $OBJECT = $DATA['name'];
        } elseif (isset($DATA['pattern'])) {
            $OBJECT = $DATA['pattern'];
        } else {
            $OBJECT = $TYPE;
        }
        try {
            $REPLY = $this->cucm->update_object_type_by_assoc($DATA, $TYPE);

            $LOG = [
                    'type'       => $TYPE,
                    'object'     => $OBJECT,
                    'status'     => 'success',
                    'reply'      => $REPLY,
                    'request'    => $DATA,

                ];

            return $LOG;
            // Create log entry
            activity('cucm_provisioning_log')->causedBy($user)->withProperties(['function' => __FUNCTION__, $LOG])->log('update object');

            return $LOG;
        } catch (\Exception $E) {
            $EXCEPTION = "Exception adding object type: {$TYPE}".
                  "{$E->getMessage()}";
                  /*"Stack trace:\n".
                  "{$E->getTraceAsString()}".
                  "Data sent:\n";*/
            //$delimiter = "Stack trace:";
            //explode ($delimiter , $EXCEPTION);
            $LOG = [
                                        'type'         => $TYPE,
                                        'object'       => $OBJECT,
                                        'status'       => 'error',
                                        'request'      => $DATA,
                                        'exception'    => $EXCEPTION,
                                    ];

            return $LOG;
        }
    }

    public function deletePhone(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('delete', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        // Check if name is Set
        if (! isset($request->name) || ! $request->name) {
            return 'Error, no name set';
        }
        $NAME = $request->name;

        $response = $this->deletePhonebyName($NAME);
        activity('cucm_provisioning_log')->causedBy($user)->withProperties(['function' => __FUNCTION__, $response])->log('delete object');

        return $response;
    }
	
	// Create New Phone
    public function createPhoneandLine(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        $errors = [];

        // Check if sitecode is Set
        if (! isset($request->sitecode) || ! $request->sitecode) {
            $errors[] = 'Error, no sitecode set';
        }
        $SITE = $request->sitecode;

        // Check if device is Set
        if (! isset($request->device) || ! $request->device) {
            $errors[] = 'Error, no device set';
        }
        $DEVICE = $request->device;

        // Check if name is Set
        if (! isset($request->name) || ! $request->name) {
            $errors[] = 'Error, no name set';
        }
        $NAME = $request->name;

        // Check if firstname is Set
        if (! isset($request->firstname) || ! $request->firstname) {
            $errors[] = 'Error, no firstname set';
        }
        $FIRSTNAME = $request->firstname;

        // Check if lastname is Set
        if (! isset($request->lastname) || ! $request->lastname) {
            $errors[] = 'Error, no lastname set';
        }
        $LASTNAME = $request->lastname;

        // Check if username is Set
        if (! isset($request->username) || ! $request->username) {
            $USERNAME = 'CallManager.Unassign';
            //return 'Error, no username set';
        } else {
            $USERNAME = $request->username;
        }

        // Check if dn is Set
        if (! isset($request->dn) || ! $request->dn) {
            $errors[] = 'Error, no dn set';
        }
        $DN = $request->dn;

        // Check if extlength is Set
        if (! isset($request->extlength) || ! $request->extlength) {
            $errors[] = 'Error, no extlength set';
        }
        $EXTENSIONLENGTH = $request->extlength;

        // Check if language is Set
        if (! isset($request->language) || ! $request->language) {
            //$errors[] = 'Error, no language set';
            $LANGUAGE = 'english';
        }
        $LANGUAGE = $request->language;

        // Check if voicemail is Set
        if (! isset($request->voicemail) || ! $request->voicemail) {
            $errors[] = 'Error, no voicemail set';
        }
        $VOICEMAIL = $request->voicemail;

        // Check if notes is Set
        if (isset($request->notes) && $request->notes) {
            $NOTES = $request->notes;
        }

        if ((isset($errors)) && ! empty($errors)) {
            $result['Phone'] = [
                        'type'         => 'Phone',
                        'object'       => $request->name,
                        'status'       => 'error',
                        'request'      => $request->all,
                        'exception'    => $errors,
                    ];

            $response = [
                        'status_code'    => 200,
                        'success'        => true,
                        'message'        => '',
                        'response'       => $result,
                        ];

            activity('cucm_provisioning_log')->causedBy($user)->withProperties(['function' => __FUNCTION__, $result])->log('add object');

            return response()->json($response);
        }

        // Final user information required to provision phone:
        $result = Cucmclass::provision_cucm_phone_axl(
                                                $SITE,
                                                $DEVICE,
                                                $NAME,
                                                $FIRSTNAME,
                                                $LASTNAME,
                                                $USERNAME,
                                                $DN,
                                                $EXTENSIONLENGTH,
                                                $LANGUAGE,
                                                $VOICEMAIL
                                            );
        $response = [
            'status_code'    => 200,
            'success'        => true,
            'message'        => '',
            'response'       => $result,
            ];

        return response()->json($response);
    }

    // Create New Phone
    public function createPhone(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        $errors = [];

        // Check if sitecode is Set
        if (! isset($request->sitecode) || ! $request->sitecode) {
            throw new \Exception('Error, no sitecode set');
        }
        $SITE = $request->sitecode;

        // Check if device is Set
        if (! isset($request->device) || ! $request->device) {
             throw new \Exception('Error, no device set');
        }
        $DEVICE = $request->device;

        // Check if name is Set
        if (! isset($request->name) || ! $request->name) {
            throw new \Exception('Error, no name set');
        }
        $NAME = $request->name;

        // Check if firstname is Set
        if (! isset($request->firstname) || ! $request->firstname) {
             throw new \Exception('Error, no firstname set');
        }
        $FIRSTNAME = $request->firstname;

        // Check if lastname is Set
        if (! isset($request->lastname) || ! $request->lastname) {
             throw new \Exception('Error, no lastname set');
        }
        $LASTNAME = $request->lastname;

        // Check if username is Set
        if (! isset($request->username) || ! $request->username) {
            $USERNAME = 'CallManager.Unassign';
            //return 'Error, no username set';
        } else {
            $USERNAME = $request->username;
        }

        // Check if dn is Set
        if (! isset($request->dn) || ! $request->dn) {
             throw new \Exception('Error, no dn set');
        }
        $DN = $request->dn;

        // Check if extlength is Set
        if (! isset($request->extlength) || ! $request->extlength) {
             throw new \Exception('Error, no extlength set');
        }
        $EXTENSIONLENGTH = $request->extlength;

        // Check if language is Set
        if (! isset($request->language) || ! $request->language) {
            //$errors[] = 'Error, no language set';
            $LANGUAGE = 'english';
        }
        $LANGUAGE = $request->language;

        // Check if voicemail is Set
        if (! isset($request->voicemail) || ! $request->voicemail) {
             throw new \Exception('Error, no voicemail set');
        }
        $VOICEMAIL = $request->voicemail;

        // Check if notes is Set
        if (isset($request->notes) && $request->notes) {
            $NOTES = $request->notes;
        }
		
		$request = $request->all();
		unset($request['token']);

        if ((isset($errors)) && ! empty($errors)) {
            $result = [
                        'type'         => 'Phone',
                        'object'       => $request->name,
                        'status'       => 'error',
                        'request'      => $request->all,
                        'exception'    => $errors,
                    ];

            $response = [
                        'status_code'    => 200,
                        'success'        => true,
                        'message'        => '',
                        'response'       => $result,
                        ];

           

            return response()->json($response);
        }
		
		
		
        // Final user information required to provision phone:
        $result = Cucmclass::add_cucm_phone(
                                                $SITE,
                                                $DEVICE,
                                                $NAME,
                                                $FIRSTNAME,
                                                $LASTNAME,
                                                $USERNAME,
                                                $DN,
                                                $EXTENSIONLENGTH,
                                                $LANGUAGE,
                                                $VOICEMAIL
                                            );
											
		activity('cucm_provisioning_log')->causedBy($user)->withProperties(['function' => __FUNCTION__, $result])->log('add object');									
											
        $response = [
            'status_code'    => 200,
            'success'        => true,
            'message'        => '',
            'response'       => $result,
            ];

        return response()->json($response);
    }
	
	// Create New Phone
    public function createLine(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        $errors = [];

        // Check if sitecode is Set
        if (! isset($request->sitecode) || ! $request->sitecode) {
            $errors[] = 'Error, no sitecode set';
        }
        $SITE = $request->sitecode;

        // Check if device is Set
        if (! isset($request->device) || ! $request->device) {
            $errors[] = 'Error, no device set';
        }
        $DEVICE = $request->device;

        // Check if name is Set
        if (! isset($request->name) || ! $request->name) {
            $errors[] = 'Error, no name set';
        }
        $NAME = $request->name;

        // Check if firstname is Set
        if (! isset($request->firstname) || ! $request->firstname) {
            $errors[] = 'Error, no firstname set';
        }
        $FIRSTNAME = $request->firstname;

        // Check if lastname is Set
        if (! isset($request->lastname) || ! $request->lastname) {
            $errors[] = 'Error, no lastname set';
        }
        $LASTNAME = $request->lastname;

        // Check if username is Set
        if (! isset($request->username) || ! $request->username) {
            $USERNAME = 'CallManager.Unassign';
            //return 'Error, no username set';
        } else {
            $USERNAME = $request->username;
        }

        // Check if dn is Set
        if (! isset($request->dn) || ! $request->dn) {
            $errors[] = 'Error, no dn set';
        }
        $DN = $request->dn;

        // Check if extlength is Set
        if (! isset($request->extlength) || ! $request->extlength) {
            $errors[] = 'Error, no extlength set';
        }
        $EXTENSIONLENGTH = $request->extlength;

        // Check if language is Set
        if (! isset($request->language) || ! $request->language) {
            //$errors[] = 'Error, no language set';
            $LANGUAGE = 'english';
        }
        $LANGUAGE = $request->language;

        // Check if voicemail is Set
        if (! isset($request->voicemail) || ! $request->voicemail) {
            $errors[] = 'Error, no voicemail set';
        }
        $VOICEMAIL = $request->voicemail;

        // Check if notes is Set
        if (isset($request->notes) && $request->notes) {
            $NOTES = $request->notes;
        }
		
		unset($request['token']);

        if ((isset($errors)) && ! empty($errors)) {
            $result['Phone'] = [
                        'type'         => 'Phone',
                        'object'       => $request->name,
                        'status'       => 'error',
                        'request'      => $request->all,
                        'exception'    => $errors,
                    ];

            $response = [
                        'status_code'    => 200,
                        'success'        => true,
                        'message'        => '',
                        'response'       => $result,
                        ];

            

            return response()->json($response);
        }
		
		$request = $request->all();

        // Clear the token, we don't want to save that data.
        unset($request['token']);
		
		
        // Final user information required to provision phone:
        $result = Cucmclass::add_cucm_line(
                                                $SITE,
                                                $DEVICE,
                                                $NAME,
                                                $FIRSTNAME,
                                                $LASTNAME,
                                                $USERNAME,
                                                $DN,
                                                $EXTENSIONLENGTH,
                                                $LANGUAGE,
                                                $VOICEMAIL
                                            );
											
		activity('cucm_provisioning_log')->causedBy($user)->withProperties(['function' => __FUNCTION__, $result])->log('add object');
		
        $response = [
            'status_code'    => 200,
            'success'        => true,
            'message'        => '',
			'request'		 => $request,
            'response'       => $result,
            ];

        return response()->json($response);
    }
}

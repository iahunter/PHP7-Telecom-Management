<?php

namespace App\Http\Controllers;

use DB;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cache;



class BouncerPermissionsController extends Controller
{
    public function getUsersPermissions()
    {
		
        $user = JWTAuth::parseToken()->authenticate();
		
		//return $user;
        if (! $user->can('read', User::class)) {
            //abort(401, 'You are not authorized');
        }

        $users = \App\User::get(); 

		$user_permissions = []; 
		
		
		foreach($users as $user){
			//print $user['username'].PHP_EOL;
			$user_permissions[$user['username']] = []; 
			//print $user->getAbilities(); 
			$abilities = $user->getAbilities(); 
			//print_r($abilities); 
			
			$permissions = []; 
			
			foreach($abilities as $ability){
				
				//print_r($ability);
				//print $ability->name.PHP_EOL;
				//print $ability->entity_id.PHP_EOL;

				// Check if the permission type (read,add,update,delete) exists in the permissions array. If not create it. 
				if(!array_key_exists($ability->name, $permissions)){
					$permissions[$ability->name] = []; 
				}
				

				// check if the type(Class) exists in the permissions array. If not create it. 
				if(!in_array($ability->entity_type, $permissions[$ability->name])){
					$permissions[$ability->name][] = $ability->entity_type;
				}
				
				// Check for specific entity ids and add them to the class they are part of. This is for Oncall permissions
				if($ability->entity_id){
					//print $ability->entity_id.PHP_EOL; 
					//$permissions[$ability->name][$ability->entity_type][] = $ability->entity_id;
					//$permissions[$ability->name][$ability->entity_type]['Oncall Line IDs'][] = $ability->entity_id;
				}
				
				
			}
			
			// Create User array with associated permissions. 
			$user_permissions[$user['username']] = $permissions; 
		}
		
		// Print User permissions.
		//print_r($user_permissions); 
        
		$response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'result'         => $user_permissions,
                    ];

        return response()->json($response);
    }
}

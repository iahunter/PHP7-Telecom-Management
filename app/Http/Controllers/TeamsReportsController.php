<?php

namespace App\Http\Controllers;

use App\Did;
use Illuminate\Http\Request;

class TeamsReportsController extends Controller
{
    //use Helpers;
    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');
    }

    public function getAllTeamsVoiceUsers()
    {
        $teamsusers = DID::where('system_id', 'like', '%MicrosoftTeams%')
                ->get();

        $response = [
            'status_code'       => 200,
            'success'           => true,
            'message'           => '',
            'response'          => $teamsusers,
        ];

        return response()->json($response);
    }
}

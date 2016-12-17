<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cupiuser;

class Unityuser extends Controller
{
    public function finduserbyalias(Request $request)
    {
        //Test
		//print_r($this->cupi->list_usertemplates());
		
		$alias = $request->alias;
		//$alias = "travis.riesenberg";
		return Cupiuser::finduserbyalias($alias);
		
    }
}

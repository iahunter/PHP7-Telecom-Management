<?php

namespace App\Http\Controllers;

use App\Cupiuser;
use Illuminate\Http\Request;

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

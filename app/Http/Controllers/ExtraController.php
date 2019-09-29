<?php

namespace App\Http\Controllers;

use App\Models\Extra\Dia;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use Carbon\Carbon;

class ExtraController extends Controller
{

    public function dias(){
        $todo = Dia::all();
        return response()->json($todo,200);
    }
}

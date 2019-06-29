<?php

namespace App\Http\Controllers;

use App\Models\Tipos\TipoContrato;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class TipoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function contratos()
    {
        $todo = TipoContrato::where('estado',1)->get();
        return response()->json($todo,200);
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\Tipos\TipoContrato;
use App\Models\Tipos\TipoMesaDocente;
use App\Models\Tipos\TipoDocenteCargo;
use App\Models\Tipos\TipoDocenteEstado;

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

    public function mesa_docente()
    {
        $todo = TipoMesaDocente::where('estado',1)->get();
        return response()->json($todo,200);
    }
    public function docente_cargo()
    {
        $todo = TipoDocenteCargo::where('estado',1)->get();
        return response()->json($todo,200);
    }

    public function docente_estado()
    {
        $todo = TipoDocenteEstado::where('estado',1)->get();
        return response()->json($todo,200);
    }
}

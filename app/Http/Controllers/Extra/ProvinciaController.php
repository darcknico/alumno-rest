<?php

namespace App\Http\Controllers\Extra;

use App\Models\Extra\Provincia;
use App\Models\Extra\Localidad;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Validator;

class ProvinciaController extends Controller
{
  public function provincias(){
    $todo = Provincia::get();
    return response()->json($todo,200);
  }

  public function localidades(Request $request){
    $termino = $request->query('termino','');
    $id_provincia = $request->query('id_provincia',0);
    $todo = Localidad::where([
      'pro_id' => $id_provincia,
      ])
      ->where(function($query) use ($termino){
        $query->where('loc_nombre','like','%'.$termino.'%')
          ->orWhere('loc_codigo_postal',$termino);
      })->orderBy('loc_nombre','asc')->limit(5)->get();
    return response()->json($todo,200);
  }
}

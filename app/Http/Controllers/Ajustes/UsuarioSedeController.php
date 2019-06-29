<?php

namespace App\Http\Controllers\Ajustes;

use App\Models\Sede;
use App\Models\UsuarioSede;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Validator;

class UsuarioSedeController extends Controller
{

    public function seleccionar(Request $request){
      $user = $request->user();
      $id_sede = $request->route('id_sede');

      $usuario = UsuarioSede::with('sede')
      ->whereHas('sede',function($q){
        return $q->where('estado',1);
      })
      ->where([
        'sed_id' => $id_sede,
        'usu_id' => $user->id,
        'estado' => 1,
      ])->first();
      if($usuario){
        $usuario->touch();

        return response()->json($usuario,200);
      } else {
        return response()->json(['error'=>'No puede seleccionar la sede'],403);
      }
    }

    public function seleccionado(Request $request){
      $user = $request->user();

      $usuario = UsuarioSede::with('sede')
      ->whereHas('sede',function($q){
        return $q->where('estado',1);
      })
      ->where([
        'usu_id' => $user->id,
        'estado' => 1,
      ])
      ->orderBy('updated_at','desc')
      ->first();
      if(!$usuario){
        return response()->json(['error'=>'No tiene ninguna sede asociada'],403);
      }

      return response()->json($usuario,200);
    }
}

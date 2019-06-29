<?php

namespace App\Http\Controllers\Ajustes;

use App\Models\Sede;
use App\Models\UsuarioSede;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Validator;

use Chatkit\Laravel\Facades\Chatkit;

class ChatController extends Controller
{

    public function token(Request $request){
      $user = $request->user();
      $id_sede = $request->query('id_sede');

      $usuario = UsuarioSede::whereHas('sede',function($q){
        return $q->where('estado',1);
      })
      ->where([
        'sed_id' => $id_sede,
        'usu_id' => $user->id,
        'estado' => 1,
      ])->first();
      if($usuario){
        $response = Chatkit::authenticate([ 'user_id' => strval($user->id) ]);
        //$response = json_decode($response,true);

        return response()->json($response['body'],200);
      } else {
        return response()->json(['error'=>'No puede seleccionar la sede'],403);
      }
    }

    public function index(Request $request){
      $user = $request->user();
      $response = Chatkit::getUser(
        [ 
          'id' => strval($user->id),
        ]
      );
      return response()->json($response,200);
    }

    public function store(Request $request){
      $user = $request->user();
      $validator = Validator::make($request->all(),[
        'nombre' => 'required',
      ]);
      if($validator->fails()){
        return response()->json(['error'=>$validator->errors()],403);
      }
      $nombre = $request->input('nombre');
      $response = Chatkit::createUser(
        [ 
          'id' => strval($user->id),
          'name' => $nombre,
        ]
      );

      return response()->json($response,200);
    }

    public function show(Request $request){
      $user = $request->chat;
      $response = Chatkit::getUser(
        [ 
          'id' => $user,
        ]
      );
      return response()->json($response,200);
    }

    public function update(Request $request){
      $user = $request->user();
      $validator = Validator::make($request->all(),[
        'nombre' => 'required',
      ]);
      if($validator->fails()){
        return response()->json(['error'=>$validator->errors()],403);
      }
      $nombre = $request->input('nombre');
      $response = Chatkit::updateUser(
        [ 
          'id' => strval($user->id),
          'name' => $nombre,
        ]
      );

      return response()->json($response,200);
    }

    public function destroy(Request $request){
      $user = $request->user();
      $response = Chatkit::deleteUser(
        [ 
          'id' => strval($user->id),
        ]
      );

      return response()->json($response,200);
    }
}

<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Modalidad;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class ModalidadController extends Controller
{
    /**
    * @OA\Get(
    *     path="/modalidades",
    *     tags={"Modalidades"},
    *     summary="Listado de modalidades",
    *     description="Mostrar todos las modalidades",
    *     operationId="index",
    *     @OA\Response(
    *         response=200,
    *         description="Mostrar todos las modalidades."
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function index(Request $request)
    {
        $id_carrera = $request->route('id_carrera');
        $todo = Modalidad::where([
            'estado' => 1 ,
        ])
            ->orderBy('created_at','desc')
            ->get();
        return response()->json($todo,200);
    }

    /**
    * @OA\Post(
    *     path="/modalidades",
    *     tags={"Modalidades"},
    *     summary="Nueva modalidad",
    *     description="Guardar nueva modalidad",
    *     operationId="create",
    *     @OA\RequestBody(
    *          description="Datos para crear una nueva modalidad",
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(ref="#/components/schemas/Modalidad")
    *          )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve un solo modalidad.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Modalidad")
    *          )
    *     ),
    *     @OA\Response(
    *         response="403",
    *         description="Error Validator."
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function store(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'nombre' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],401);
        }
        $nombre = $request->input('nombre');
        $descripcion = $request->input('descripcion');

        $todo = new Modalidad;
        $todo->mod_nombre = $nombre;
        $todo->mod_descripcion = $descripcion;
        $todo->usu_id = $user->id;
        $todo->save();
        
        return response()->json($todo,200);
    }

    /**
    * @OA\Get(
    *     path="/modalidades/{id_modalidad}",
    *     tags={"Modalidades"},
    *     summary="Mostrar modalidad",
    *     description="Recupera la modalidad de acuerdo al id",
    *     operationId="show",
    *     @OA\Parameter(ref="#/components/parameters/id_modalidad"),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve un solo modalidad.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Modalidad")
    *          )
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function show(Request $request)
    {
        $id_modalidad = $request->route('id_modalidad');
        $todo = Modalidad::with('usuario')->where('mod_id',$id_modalidad)->first();
        return response()->json($todo,200);
    }

    /**
    * @OA\Put(
    *     path="/modalidades/{id_modalidad}",
    *     tags={"Modalidades"},
    *     summary="Editar modalidad",
    *     description="Edita la modalidad de acuerdo al id",
    *     operationId="update",
    *     @OA\Parameter(ref="#/components/parameters/id_modalidad"),
    *     @OA\RequestBody(
    *          description="Datos del modalidad",
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(ref="#/components/schemas/Modalidad")
    *          )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve una sola modalidad.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Modalidad")
    *          )
    *     ),
    *     @OA\Response(
    *         response="403",
    *         description="Error Validator."
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function update(Request $request)
    {
        $user = Auth::user();
        $id_modalidad = $request->route('id_modalidad');
        $validator = Validator::make($request->all(),[
            'nombre' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],401);
        }
        $nombre = $request->input('nombre');
        $descripcion = $request->input('descripcion');

        $todo = Modalidad::where('mod_id',$id_modalidad)->first();
        if($todo){
            $todo->mod_nombre = $nombre;
            $todo->mod_descripcion = $descripcion;
            $todo->save();
        } 
        return response()->json($todo,200);
    }

    /**
    * @OA\Delete(
    *     path="/modalidades/{id_modalidad}",
    *     tags={"Modalidades"},
    *     summary="Eliminar modalidad",
    *     description="Elimina la modalidad de acuerdo al id",
    *     operationId="destroy",
    *     @OA\Parameter(ref="#/components/parameters/id_modalidad"),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve un solo una modalidad.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Modalidad")
    *          )
    *     ),
    * )
    */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $id_modalidad = $request->route('id_modalidad');

        $todo = Modalidad::where('mod_id_',$id_modalidad)->first();
        if($todo){
            $todo->estado = 0;
            $todo->save();
        }
        return response()->json($todo,200);
    }
}

<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Departamento;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class DepartamentoController extends Controller
{
    /**
    * @OA\Get(
    *     path="/departamentos",
    *     tags={"Departamentos"},
    *     summary="Listado de departamentos",
    *     description="Mostrar todos los departamentos",
    *     operationId="index",
    *     @OA\Response(
    *         response=200,
    *         description="Mostrar todos los departamentos."
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function index(Request $request)
    {
        $todo = Departamento::where([
            'estado' => 1 ,
        ])->get();
        return response()->json($todo,200);
    }

    /**
    * @OA\Post(
    *     path="/departamentos",
    *     tags={"Departamentos"},
    *     summary="Nuevo departamento",
    *     description="Guardar nuevo departamento",
    *     operationId="create",
    *     @OA\RequestBody(
    *          description="Datos para crear un nuevo departamento",
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(ref="#/components/schemas/Departamento")
    *          )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve un solo departamento.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Departamento")
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

        $todo = new Departamento;
        $todo->dep_nombre = $nombre;
        $todo->sed_id = 0;
        $todo->usu_id = $user->id;
        $todo->save();
        
        return response()->json($todo,200);
    }

    /**
    * @OA\Get(
    *     path="/departamentos/{id_departamento}",
    *     tags={"Departamentos"},
    *     summary="Mostrar departamento",
    *     description="Recupera el departamento de acuerdo al id",
    *     operationId="show",
    *     @OA\Parameter(ref="#/components/parameters/id_departamento"),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve un solo departamento.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Departamento")
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
        $id_departamento = $request->route('id_departamento');
        $todo = Departamento::with('carreras','usuario','sede')->where('dep_id',$id_departamento)->first();
        return response()->json($todo,200);
    }

    /**
    * @OA\Put(
    *     path="/departamentos/{id_departamento}",
    *     tags={"Departamentos"},
    *     summary="Editar departamento",
    *     description="Edita el departamento de acuerdo al id",
    *     operationId="update",
    *     @OA\Parameter(ref="#/components/parameters/id_departamento"),
    *     @OA\RequestBody(
    *          description="Datos del departamento",
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(ref="#/components/schemas/Departamento")
    *          )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve un solo departamento.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Departamento")
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
        $id_departamento = $request->route('id_departamento');
        $validator = Validator::make($request->all(),[
            'nombre' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],401);
        }
        $nombre = $request->input('nombre');
        $todo = Departamento::where('dep_id',$id_departamento)->first();
        if($todo){
            $todo->dep_nombre = $nombre;
            $todo->save();
        } 
        return response()->json($todo,200);
    }

    /**
    * @OA\Delete(
    *     path="/departamentos/{id_departamento}",
    *     tags={"Departamentos"},
    *     summary="Eliminar departamento",
    *     description="Elimina al departamento de acuerdo al id",
    *     operationId="destroy",
    *     @OA\Parameter(ref="#/components/parameters/id_departamento"),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve un solo departamento.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Departamento")
    *          )
    *     ),
    * )
    */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $id_departamento = $request->route('id_departamento');

        $todo = Departamento::where('dep_id',$id_departamento)->first();
        if($todo){
            $todo->estado = 0;
            $todo->save();
        }
        return response()->json($todo,200);
    }
}

<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Beca;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class BecaController extends Controller
{
    /**
    * @OA\Get(
    *     path="/becas",
    *     tags={"Becas"},
    *     summary="Listado de beca",
    *     description="Mostrar todos las beca",
    *     operationId="index",
    *     @OA\Response(
    *         response=200,
    *         description="Mostrar todos las beca."
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
        $todo = Beca::where([
            'estado' => 1 ,
        ])
            ->orderBy('nombre','desc')
            ->get();
        return response()->json($todo,200);
    }

    /**
    * @OA\Post(
    *     path="/becas",
    *     tags={"Becas"},
    *     summary="Nueva beca",
    *     description="Guardar nueva beca",
    *     operationId="create",
    *     @OA\RequestBody(
    *          description="Datos para crear una nueva beca",
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(ref="#/components/schemas/Beca")
    *          )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve un solo beca.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Beca")
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
            'porcentaje' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],401);
        }
        $nombre = $request->input('nombre');
        $descripcion = $request->input('descripcion');
        $porcentaje = $request->input('porcentaje');

        $todo = new Beca;
        $todo->nombre = $nombre;
        $todo->descripcion = $descripcion;
        $todo->porcentaje = $porcentaje;
        $todo->usu_id = $user->id;
        $todo->save();
        
        return response()->json($todo,200);
    }

    /**
    * @OA\Get(
    *     path="/becas/{id_beca}",
    *     tags={"Becas"},
    *     summary="Mostrar beca",
    *     description="Recupera la beca de acuerdo al id",
    *     operationId="show",
    *     @OA\Parameter(ref="#/components/parameters/id_beca"),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve un solo beca.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Beca")
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
        $id_beca = $request->route('id_beca');
        $todo = Beca::find($id_beca);
        return response()->json($todo,200);
    }

    /**
    * @OA\Put(
    *     path="/becas/{id_beca}",
    *     tags={"Becas"},
    *     summary="Editar beca",
    *     description="Edita la beca de acuerdo al id",
    *     operationId="update",
    *     @OA\Parameter(ref="#/components/parameters/id_beca"),
    *     @OA\RequestBody(
    *          description="Datos del beca",
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(ref="#/components/schemas/Beca")
    *          )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve una sola beca.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Beca")
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
        $id_beca = $request->route('id_beca');
        $validator = Validator::make($request->all(),[
            'nombre' => 'required',
            'porcentaje' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],401);
        }
        $nombre = $request->input('nombre');
        $descripcion = $request->input('descripcion');
        $porcentaje = $request->input('porcentaje');

        $todo = Beca::find($id_beca);
        if($todo){
            $todo->nombre = $nombre;
            $todo->descripcion = $descripcion;
            $todo->porcentaje = $porcentaje;
            $todo->save();
        } 
        return response()->json($todo,200);
    }

    /**
    * @OA\Delete(
    *     path="/becas/{id_beca}",
    *     tags={"Becas"},
    *     summary="Eliminar beca",
    *     description="Elimina la beca de acuerdo al id",
    *     operationId="destroy",
    *     @OA\Parameter(ref="#/components/parameters/id_beca"),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve un solo una beca.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Beca")
    *          )
    *     ),
    * )
    */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $id_beca = $request->route('id_beca');

        $todo = Beca::find($id_beca);
        if($todo){
            $todo->estado = 0;
            $todo->save();
        }
        return response()->json($todo,200);
    }
}

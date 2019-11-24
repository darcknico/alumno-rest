<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Sede;
use App\Models\UsuarioSede;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;


/**
* Class SedeController
* @package App\Http\Controllers
*/
class SedeController extends Controller
{
    /**
    * @OA\Get(
    *     path="/sedes",
    *     tags={"Sedes"},
    *     summary="Listado de sedes",
    *     description="Mostrar todas las sedes",
    *     operationId="index",
    *     @OA\Response(
    *         response=200,
    *         description="listado de todas las sedes."
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function index(Request $request)
    {
        $user = Auth::user();

        $registros = Sede::with([
            'departamentos'=>function($q){
                $q->where('estado',1);
            }
        ])->where('estado',1);

        if($user->tus_id == 1){
            $todo = $registros
                ->orderBy('sed_nombre','desc')
                ->get();
            return response()->json($todo,200);
        }

        $sedes = UsuarioSede::where([
          'usu_id' => $user->id,
          'estado' => 1,
        ])->pluck('sed_id')->toArray();
        $todo = $registros
        ->whereIn('sed_id',$sedes)
        ->orderBy('sed_nombre','desc')
        ->get();
        return response()->json($todo,200);
    }

    public function buscar(Request $request)
    {
        $termino = $request->query('termino','');
        $todo = Sede::where('estado',1)
          ->where(function($query) use ($termino){
            $query
                ->where('sed_localidad','like','%'.$termino.'%')
                ->orWhere('sed_nombre','like','%'.$termino.'%');
          })
          ->orderBy('sed_nombre','desc')
          ->limit(5)
          ->get();
        return response()->json($todo,200);
    }

    /**
    * @OA\Post(
    *     path="/sedes",
    *     tags={"Sedes"},
    *     summary="Nueva sede",
    *     description="Guardar nueva sede",
    *     operationId="create",
    *     @OA\RequestBody(
    *          description="Datos para crear nueva sede",
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(ref="#/components/schemas/Sede")
    *          )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve la sede creada.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Sede")
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
        $id_localidad = $request->input('id_localidad');
        $localidad = $request->input('localidad');
        $ubicacion = $request->input('ubicacion');
        $latitud = $request->input('latitud');
        $longitud = $request->input('longitud');
        $codigo_postal = $request->input('codigo_postal');
        $direccion = $request->input('direccion');
        $telefono = $request->input('telefono');
        $celular = $request->input('celular');
        $email = $request->input('email');
        $punto_venta = $request->input('punto_venta');

        $todo = new Sede;
        $todo->sed_nombre = $nombre;
        $todo->loc_id = $id_localidad;
        $todo->sed_localidad = $localidad;
        $todo->sed_ubicacion = $ubicacion;
        $todo->sed_latitud = $latitud;
        $todo->sed_longitud = $longitud;
        $todo->sed_codigo_postal = $codigo_postal;
        $todo->sed_direccion = $direccion;
        $todo->sed_telefono = $telefono;
        $todo->sed_celular = $celular;
        $todo->sed_email = $email;
        $todo->punto_venta = $punto_venta;
        $todo->usu_id = $user->id;
        $todo->save();

        return response()->json($todo,200);
    }

    /**
    * @OA\Get(
    *     path="/sedes/{id_sede}",
    *     tags={"Sedes"},
    *     summary="Mostrar Sede",
    *     description="Recupera la sede de acuerdo al id",
    *     operationId="show",
    *     @OA\Parameter(ref="#/components/parameters/id_sede"),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve una sola sede.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Alumno")
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
        $id_sede = $request->route('id_sede');
        $sede = Sede::with('departamentos','usuario')->where('sed_id',$id_sede)->first();
        return response()->json($sede,200);
    }

    /**
    * @OA\Put(
    *     path="/sedes/{id_sede}",
    *     tags={"Sedes"},
    *     summary="Editar sede",
    *     description="Edita la sede de acuerdo al id",
    *     operationId="update",
    *     @OA\Parameter(ref="#/components/parameters/id_sede"),
    *     @OA\RequestBody(
    *          description="Datos de la sede",
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(ref="#/components/schemas/Sede")
    *          )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve una sola sede.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Sede")
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
        $id_sede = $request->route('id_sede');
        $validator = Validator::make($request->all(),[
            'nombre' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],401);
        }
        $nombre = $request->input('nombre');
        $id_localidad = $request->input('id_localidad');
        $localidad = $request->input('localidad');
        $ubicacion = $request->input('ubicacion');
        $latitud = $request->input('latitud');
        $longitud = $request->input('longitud');
        $codigo_postal = $request->input('codigo_postal');
        $direccion = $request->input('direccion');
        $telefono = $request->input('telefono');
        $celular = $request->input('celular');
        $email = $request->input('email');
        $punto_venta = $request->input('punto_venta');

        $sede = Sede::where('sed_id',$id_sede)->first();
        if($sede){
            $sede->sed_nombre = $nombre;
            $sede->loc_id = $id_localidad;
            $sede->sed_localidad = $localidad;
            $sede->sed_ubicacion = $ubicacion;
            $sede->sed_latitud = $latitud;
            $sede->sed_longitud = $longitud;
            $sede->sed_codigo_postal = $codigo_postal;
            $sede->sed_direccion = $direccion;
            $sede->sed_telefono = $telefono;
            $sede->sed_celular = $celular;
            $sede->sed_email = $email;
            $sede->punto_venta = $punto_venta;
            $sede->save();
        } 
        return response()->json($sede,200);
    }

    /**
    * @OA\Delete(
    *     path="/sedes/{id_sede}",
    *     tags={"Sedes"},
    *     summary="Eliminar sede",
    *     description="Elimina la sede",
    *     operationId="destroy",
    *     @OA\Parameter(ref="#/components/parameters/id_sede"),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve una sola sede.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Sede")
    *          )
    *     )
    * )
    */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $id_sede = $request->route('id_sede');

        $sede = Sede::where('sed_id',$id_sede)->first();
        if($sede){
            $sede->estado = 0;
            $sede->save();
        }
        return response()->json($sede,200);
    }
}

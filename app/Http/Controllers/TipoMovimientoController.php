<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\TipoMovimiento;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class TipoMovimientoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $id_sede = $request->route('id_sede');

        $todo = TipoMovimiento::where([
            'estado' => 1 ,
        ])
            ->whereIn('sed_id',[0,$id_sede])
            ->orderBy('created_at','desc')
            ->get();
        return response()->json($todo,200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id_sede = $request->route('id_sede');
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'nombre' => 'required',
            'id_tipo_egreso_ingreso' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $nombre = $request->input('nombre');
        $descripcion = $request->input('descripcion');
        $id_tipo_egreso_ingreso = $request->input('id_tipo_egreso_ingreso');

        $todo = new TipoMovimiento;
        $todo->nombre = $nombre;
        $todo->descripcion = $descripcion;
        $todo->id_tipo_egreso_ingreso = $id_tipo_egreso_ingreso;
        $todo->usu_id = $user->id;
        $todo->id_sede = $id_sede;
        $todo->save();
        
        return response()->json($todo,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id_tipo_movimiento = $request->route('id_tipo_movimiento');
        $todo = TipoMovimiento::with('usuario')->find($id_tipo_movimiento);
        return response()->json($todo,200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function edit(TipoUsuario $tipoUsuario)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $id_tipo_movimiento = $request->route('id_tipo_movimiento');
        $validator = Validator::make($request->all(),[
            'nombre' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $nombre = $request->input('nombre');
        $descripcion = $request->input('descripcion');

        $todo = TipoMovimiento::find($id_tipo_movimiento);
        if($todo){
            $todo->nombre = $nombre;
            $todo->descripcion = $descripcion;
            $todo->save();
        } 
        return response()->json($todo,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $id_tipo_movimiento = $request->route('id_tipo_movimiento');

        $todo = TipoMovimiento::find($id_tipo_movimiento);
        if($todo->id_sede == 0){
            if($validator->fails()){
              return response()->json(['error'=>'El tipo de movimiento no puede ser eliminado.'],403);
            }
        }
        if($todo){
            $todo->estado = 0;
            $todo->deleted_at = Carbon::now();
            $todo->id_usuario_baja = $user->id;
            $todo->save();
        }
        return response()->json($todo,200);
    }

    public function ingresos(Request $request)
    {
        $id_sede = $request->route('id_sede');

        $todo = TipoMovimiento::where([
            'estado' => 1 ,
            'tei_id' => 1,
        ])
        ->whereIn('sed_id',[0,$id_sede])
        ->orderBy('nombre','asc')
        ->get();
        return response()->json($todo,200);
    }

    public function egresos(Request $request)
    {
        $id_sede = $request->route('id_sede');

        $todo = TipoMovimiento::where([
            'estado' => 1 ,
            'tei_id' => 0,
        ])
        ->whereIn('sed_id',[0,$id_sede])
        ->orderBy('nombre','asc')
        ->get();
        return response()->json($todo,200);
    }
}

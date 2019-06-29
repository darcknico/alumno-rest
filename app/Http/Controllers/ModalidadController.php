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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
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
     * Display the specified resource.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id_modalidad = $request->route('id_modalidad');
        $todo = Modalidad::with('usuario')->where('mod_id',$id_modalidad)->first();
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
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

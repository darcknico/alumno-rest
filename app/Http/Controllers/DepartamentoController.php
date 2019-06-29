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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $todo = Departamento::where([
            'estado' => 1 ,
        ])->get();
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

        $todo = new Departamento;
        $todo->dep_nombre = $nombre;
        $todo->sed_id = 0;
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
        $id_departamento = $request->route('id_departamento');
        $todo = Departamento::with('carreras','usuario','sede')->where('dep_id',$id_departamento)->first();
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
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

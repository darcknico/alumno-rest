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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
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
     * Display the specified resource.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id_beca = $request->route('id_beca');
        $todo = Beca::find($id_beca);
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
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

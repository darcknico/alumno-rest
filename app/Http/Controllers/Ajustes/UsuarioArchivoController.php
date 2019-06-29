<?php

namespace App\Http\Controllers\Ajustes;

use App\Models\UsuarioArchivo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Validator;

class UsuarioArchivoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $id_usuario = $request->route('id_usuario');
        $todo = UsuarioArchivo::where('id_usuario',$id_usuario)->where('estado',1)->get();
        return response()->json($todo,200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id_usuario = $request->route('id_usuario');
        $validator = Validator::make($request->all(),[
            'archivo' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $todo = null;
        if($request->hasFile('archivo')){
            $archivo = $request->file('archivo');
            $filename = $archivo->store('usuarios/archivos');
            $todo = new UsuarioArchivo;
            $todo->nombre = $archivo->getClientOriginalName();
            $todo->uar_dir = $filename;
            $todo->id_usuario = $id_usuario;
            $todo->save();

        }
        return response()->json($todo,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UsuarioArchivo  $usuarioArchivo
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $usuarioArchivo = UsuarioArchivo::find($request->archivo);
        return response()->download(storage_path("app/{$usuarioArchivo->uar_dir}"));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UsuarioArchivo  $usuarioArchivo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UsuarioArchivo $usuarioArchivo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UsuarioArchivo  $usuarioArchivo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $usuarioArchivo = UsuarioArchivo::find($request->archivo);
        Storage::delete($usuarioArchivo->uar_dir);
        $usuarioArchivo->estado = 0;
        $usuarioArchivo->save();
        return response()->json($usuarioArchivo,200);
    }
}

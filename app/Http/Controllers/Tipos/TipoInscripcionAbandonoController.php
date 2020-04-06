<?php

namespace App\Http\Controllers\Tipos;

use App\Models\Tipos\TipoInscripcionAbandono;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Validator;
use Carbon\Carbon;

class TipoInscripcionAbandonoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $todo = TipoInscripcionAbandono::where([
            'estado' => 1 ,
        ])
            ->orderBy('nombre','desc')
            ->get();
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
        $validator = Validator::make($request->all(),[
            'nombre' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],401);
        }
        $nombre = $request->input('nombre');
        $descripcion = $request->input('descripcion');

        $todo = new TipoInscripcionAbandono;
        $todo->nombre = $nombre;
        $todo->descripcion = $descripcion;
        $todo->save();
        return response()->json($todo,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tipos\TipoInscripcionAbandono  $tipoInscripcionAbandono
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        return response()->json($request->abandono,200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tipos\TipoInscripcionAbandono  $tipoInscripcionAbandono
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nombre' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],401);
        }
        $nombre = $request->input('nombre');
        $descripcion = $request->input('descripcion');

        $tipoInscripcionAbandono = TipoInscripcionAbandono::find($request->abandono);
        $tipoInscripcionAbandono->nombre = $nombre;
        $tipoInscripcionAbandono->descripcion = $descripcion;
        $tipoInscripcionAbandono->save();
        return response()->json($tipoInscripcionAbandono,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tipos\TipoInscripcionAbandono  $tipoInscripcionAbandono
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $tipoInscripcionAbandono = TipoInscripcionAbandono::find($request->abandono);
        $tipoInscripcionAbandono->estado = 0;
        $tipoInscripcionAbandono->save();
        return response()->json($tipoInscripcionAbandono,200);
    }
}

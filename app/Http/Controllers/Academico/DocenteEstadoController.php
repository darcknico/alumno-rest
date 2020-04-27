<?php

namespace App\Http\Controllers\Academico;

use App\Models\Academico\DocenteEstado;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocenteEstadoRequest;
use Illuminate\Support\Facades\Storage;

class DocenteEstadoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);
        $registros = DocenteEstado::with('docente');

        $id_usuario = $request->query('id_usuario',0);
        $id_tipo_docente_estado = $request->query('id_tipo_docente_estado',0);

        $registros = $registros
            ->when($id_usuario>0,function($q)use($id_usuario){
                return $q->where('id_usuario',$id_usuario);
            })
            ->when($id_tipo_docente_estado>0,function($q)use($id_tipo_docente_estado){
                return $q->where('id_tipo_docente_estado',$id_tipo_docente_estado);
            });

        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros->orderBy('created_at','desc')
            ->get();
            return response()->json($todo,200);
        }
        
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
              if(strlen($value)>0){
                $registros = $registros->where(function($query) use  ($value) {
                  
                });
              }
            }
        }
        if(strlen($sort)>0){
        $registros = $registros->orderBy($sort,$order);
        } else {
        $registros = $registros->orderBy('created_at','desc');
        }
        $q = clone($registros->getQuery());
        $total_count = count($q->get());
        if($length>0){
        $registros = $registros->limit($length);
        if($start>1){
            $registros = $registros->offset($start)->get();
        } else {
            $registros = $registros->get();
        }

        } else {
            $registros = $registros->get();
        }

        return response()->json([
            'total_count'=>intval($total_count),
            'items'=>$registros,
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DocenteEstadoRequest $request)
    {
        $docenteEstado = new DocenteEstado;
        $docenteEstado->id_usuario = $request->id_usuario;
        $docenteEstado->fecha_inicial = $request->fecha_inicial;
        $docenteEstado->fecha_final = $request->fecha_final;
        $docenteEstado->id_tipo_docente_estado = $request->id_tipo_docente_estado;
        $docenteEstado->observaciones = $request->observaciones;

        if($request->hasFile('archivo')){
            $archivo = $request->file('archivo');
            $filename = $archivo->store('docentes/archivos');

            $docenteEstado->archivo = $archivo->getClientOriginalName();
            $docenteEstado->des_dir = $filename;
        }
        $docenteEstado->save();
        return response()->json($docenteEstado);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Academico\DocenteEstado  $docenteEstado
     * @return \Illuminate\Http\Response
     */
    public function show(DocenteEstado $docenteEstado)
    {
        return response()->json($docenteEstado);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Academico\DocenteEstado  $docenteEstado
     * @return \Illuminate\Http\Response
     */
    public function update(DocenteEstadoRequest $request, DocenteEstado $docenteEstado)
    {
        $docenteEstado->fecha_inicial = $request->fecha_inicial;
        $docenteEstado->fecha_final = $request->fecha_final;
        $docenteEstado->observaciones = $request->observaciones;
        $docenteEstado->save();
        return response()->json($docenteEstado);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Academico\DocenteEstado  $docenteEstado
     * @return \Illuminate\Http\Response
     */
    public function destroy(DocenteEstado $docenteEstado)
    {
        Storage::delete($docenteEstado->des_dir);
        $docenteEstado->delete();

        return response()->json($docenteEstado);
    }

    public function archivo(Request $request){
        $id_docente_estado = $request->route('id_docente_estado');
        $todo = DocenteEstado::find($id_docente_estado);
        return response()->download(storage_path("app/{$todo->des_dir}"),$todo->archivo);
    }
}

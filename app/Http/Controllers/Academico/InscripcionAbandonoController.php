<?php

namespace App\Http\Controllers\Academico;

use App\Models\Inscripcion;
use App\Models\Academico\InscripcionAbandono;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Validator;
use Carbon\Carbon;

class InscripcionAbandonoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $id_sede = $request->route('id_sede');

        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);
        $registros = InscripcionAbandono::with([
            'inscripcion.alumno',
            'inscripcion.carrera',
            'usuario',
        ])
        ->where([
            'estado' => 1,
        ]);
        
        $id_inscripcion = $request->query('id_inscripcion',0);
        $id_tipo_inscripcion_abandono = $request->query('id_tipo_inscripcion_abandono',0);
        $id_alumno = $request->query('id_alumno',0);

        $registros = $registros
            ->when($id_sede>0,function($q)use($id_sede){
                $q->whereHas('inscripcion',function($qt)use($id_sede){
                  $qt->where('id_sede','=',$id_sede);
                });
            })
            ->when($id_inscripcion>0,function($q)use($id_inscripcion){
                $q->where('id_inscripcion',$id_inscripcion);
            })
            ->when($id_tipo_inscripcion_abandono>0,function($q)use($id_tipo_inscripcion_abandono){
                $q->where('id_tipo_inscripcion_abandono',$id_tipo_inscripcion_abandono);
            })
            ->when($id_alumno>0,function($q)use($id_alumno){
                $q->whereHas('inscripcion',function($qt)use($id_alumno){
                    $qt->where('id_alumno','=',$id_alumno);
                });
            });
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
              if(strlen($value)>0){

              }
            }
        }
        if( strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros->orderBy('created_at','desc')
            ->get();
            return response()->json($todo,200);
        }
        if(strlen($sort)>0){
        $registros = $registros->orderBy($sort,$order);
        } else {
        $registros = $registros->orderBy('created_at','desc');
        }
        $sql = $registros->toSql();
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
    public function store(Request $request)
    {
        $id_inscripcion = $request->route('id_inscripcion');

        $validator = Validator::make($request->all(),[
          "tipo_abandonos.*"  => "nullable|integer|distinct",
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $abandonos = $request->input('tipo_abandonos');

        $inscripcion = Inscripcion::find($id_inscripcion);
        if(!$inscripcion){
            return response()->json(['error'=>'La inscripcion no fue encontrada.'],403);
        }
        InscripcionAbandono::where('estado',1)
          ->where('id_inscripcion',$id_inscripcion)
          ->update([
            'estado' => 0,
          ]);
        foreach ($abandonos as $abandono) {
          $todo = new InscripcionAbandono;
          $todo->id_inscripcion = $id_inscripcion;
          $todo->id_tipo_inscripcion_abandono = $abandono;
          $todo->save();
            
        }
        $inscripcion->id_tipo_inscripcion_estado = 3;
        $inscripcion->save();
        return response()->json($inscripcion,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Academico\InscripcionAbandono  $inscripcionAbandono
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Academico\InscripcionAbandono  $inscripcionAbandono
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Academico\InscripcionAbandono  $inscripcionAbandono
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $alumno = InscripcionAbandono::find($request->inscripcionAbandono);
        $alumno->estado = 0;
        $alumno->save();
        return response()->json($alumno,200);
    }
}

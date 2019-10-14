<?php

namespace App\Http\Controllers\Academico;

use App\Models\Alumno;
use App\Models\Sede;
use App\Models\Academico\AlumnoSede;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Validator;

class AlumnoSedeController extends Controller
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
        $registros = AlumnoSede::with([
            'alumno.sede',
            'sede',
            'usuario',
        ])
        ->where([
            'estado' => 1,
        ]);
        
        $id_sede = $request->query('id_sede',0);
        $id_alumno = $request->query('id_alumno',0);
        $documento = $request->query('documento',0);

        $registros = $registros
            ->when($id_sede>0,function($q)use($id_sede){
                return $q->where('id_sede',$id_sede)->whereHas('alumno',function($qt)use($id_sede){
                  $qt->where('id_sede','!=',$id_sede);
                });
            })
            ->when($id_alumno>0,function($q)use($id_alumno){
              $q->where('id_alumno',$id_alumno);
            })
            ->when($documento>0,function($q)use($documento){
              $q->whereHas('alumno',function($qt)use($documento){
                $qt->where('documento',$documento);
              });
            });
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
              if(strlen($value)>0){
                $registros = $registros->where(function($query) use  ($value) {
                  $query->whereHas('alumno',function($q)use($value){
                    $q->where('documento','like','%'.$value.'%');
                  });
                });
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
        $validator = Validator::make($request->all(),[
          'id_alumno' => 'required | integer',
          'id_sede' => 'required | integer',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $id_alumno = $request->input('id_alumno');
        $id_sede = $request->input('id_sede');

        $alumno = Alumno::find($id_alumno);
        if(!$alumno){
            return response()->json(['error'=>'El alumno no fue encontrado.'],403);
        }

        $sede = Sede::find($id_sede);
        if(!$sede){
            return response()->json(['error'=>'La sede no fue encontrada.'],403);
        }

        $registro = AlumnoSede::where('id_alumno',$id_alumno)->where('id_sede',$id_sede)->where('estado',1)->first();
        if($registro){
          return response()->json($registro,200);
        }

        $todo = new AlumnoSede;
        $todo->id_alumno = $id_alumno;
        $todo->id_sede = $id_sede;
        $todo->save();

        return response()->json($todo,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Academico\AlumnoSede  $alumnoSede
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $alumno = AlumnoSede::with('alumno','sede','usuario')->find($request->alumnoSede);
        return response()->json($alumno,200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Academico\AlumnoSede  $alumnoSede
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Academico\AlumnoSede  $alumnoSede
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $alumno = AlumnoSede::find($request->alumnoSede);
        $alumno->estado = 0;
        $alumno->save();
        return response()->json($alumno,200);
    }
}

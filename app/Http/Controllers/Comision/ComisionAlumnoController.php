<?php

namespace App\Http\Controllers\Comision;

use App\Models\Carrera;
use App\Models\ComisionAlumno;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;

class ComisionAlumnoController extends Controller
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
        
        $registros = ComisionAlumno::with('comision.materia')
            ->whereHas('comision',function($q)use($id_sede){
                $q->where([
                    'estado' => 1,
                    'sed_id' => $id_sede,
                ]);
            })
            ->where([
            'estado' => 1,
        ]);

        $id_departamento = $request->query('id_departamento',0);
        $id_carrera = $request->query('id_carrera',0);
        $id_materia = $request->query('id_materia',0);
        $id_comision = $request->query('id_comision',0);
        $id_alumno = $request->query('id_alumno',0);
        $id_inscripcion = $request->query('id_inscripcion',0);
        $anio = $request->query('anio',null);

        $registros = $registros
            ->when($id_departamento>0,function($q)use($id_departamento){
                $carreras = Carrera::where([
                    'dep_id' => $id_departamento,
                    'estado' => 1,
                ])->pluck('car_id')->toArray();
                return $q->whereHas('comision',function($qt)use($carreras){
                    $qt->whereIn('car_id',$carreras);
                });
            })
            ->when($id_carrera>0,function($q)use($id_carrera){
                return $q->whereHas('comision',function($qt)use($id_carrera){
                    $qt->where('car_id',$id_carrera);
                });
            })
            ->when($id_materia>0,function($q)use($id_materia){
                return $q->whereHas('comision',function($qt)use($id_materia){
                    $qt->where('mat_id',$id_carrera);
                });
            })
            ->when(!empty($anio) and $anio>0,function($q)use($anio){
                return $q->whereHas('comision',function($qt)use($anio){
                    $qt->where('com_anio',$anio);
                });
            })
            ->when($id_comision>0,function($q)use($id_comision){
                return $q->where('id_comision',$id_comision);
            })
            ->when($id_alumno>0,function($q)use($id_alumno){
                return $q->where('id_alumno',$id_alumno);
            })
            ->when($id_inscripcion>0,function($q)use($id_inscripcion){
                return $q->where('id_inscripcion',$id_inscripcion);
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
                        $query->whereHas('alumno',function($q)use($value){
                            $q->where('nombre','like','%'.$value.'%')
                            ->orWhere('apellido','like','%'.$value.'%')
                            ->orWhere('documento','like','%'.$value.'%');
                        });
                    });
                }
            }
        }

        $sql = $registros->toSql();
        $q = clone($registros->getQuery());
        $total_count = $q->groupBy('estado')->count();

        if(strlen($sort)>0){
            $registros = $registros->orderBy($sort,$order);
        } else {
            $registros = $registros->orderBy('created_at','desc');
        }
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
        $id_sede = $request->route('id_sede');
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'id_comision' => 'required',
            'id_alumno' => 'required',
            'id_inscripcion' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $id_comision = $request->input('id_comision');
        $id_alumno = $request->input('id_alumno');
        $id_inscripcion = $request->input('id_inscripcion');
        $nota = $request->input('nota',null);
        $id_tipo_condicion_alumno = $request->input('id_tipo_condicion_alumno',null);
        $observaciones = $request->input('observaciones',null);

        $comision = Comision::find($id_comision);
        if(!$comision){
            return response()->json(['error'=>'La comision no fue encontrada.'],403);
        }
        $alumno = Alumno::find($id_alumno);
        if(!$alumno){
            return response()->json(['error'=>'El alumno no fue encontrado.'],403);
        }
        $inscripcion = Inscripcion::find($id_inscripcion);
        if(!$inscripcion){
            return response()->json(['error'=>'La inscripcion no fue encontrada.'],403);
        }
        if($comision->id_carrera != $inscripcion->id_carrera){
            return response()->json(['error'=>'El alumno no pertenece a la misma carrera que la comision.'],403);
        }

        $todo = ComisionAlumno::where([
            'estado' => 1,
            'alu_id' => $id_alumno,
            'com_id' => $id_comision,
        ])->first();
        if($todo){
            return response()->json(['error'=>'La inscripcion a la comision ya existe.'],403);
        } else {
            $todo = new ComisionAlumno;
            $todo->id_comision = $id_comision;
            $todo->id_alumno = $id_alumno;
            $todo->id_inscripcion = $id_inscripcion;
            $todo->nota = $nota;
            $todo->id_tipo_condicion_alumno = $id_tipo_condicion_alumno;
            $todo->observaciones = $observaciones;
            $todo->usu_id = $user->id;
            $todo->save();
        }
        $alumnos_cantidad = ComisionAlumno::selectRaw('count(*) as total')
            ->where([
                'estado' => 1,
                'com_id' => $id_comision,
            ])->groupBy('com_id')->first();
        $comision->alumnos_cantidad = $alumnos_cantidad->total??0;
        $comision->save();

        return response()->json($todo,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ComisionAlumno  $comisionAlumno
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $todo = ComisionAlumno::with('comision.materia')->find($request->comisionAlumno);
        return response()->json($todo,200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ComisionAlumno  $comisionAlumno
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nota' => 'integer | nullable',
            'id_tipo_condicion_alumno' => 'integer | nullable',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $todo = ComisionAlumno::find($request->comisionAlumno);

        $nota = $request->input('nota',null);
        $id_tipo_condicion_alumno = $request->input('id_tipo_condicion_alumno',null);
        $observaciones = $request->input('observaciones',null);

        $todo->nota = $nota;
        $todo->id_tipo_condicion_alumno = $id_tipo_condicion_alumno;
        $todo->observaciones = $observaciones;
        $todo->save();
        return response()->json($todo,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ComisionAlumno  $comisionAlumno
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $todo = ComisionAlumno::find($request->comisionAlumno);

        $todo->estado = 0;
        $todo->usu_id_baja = $user->id;
        $todo->deleted_at = Carbon::now();
        $todo->save();

        $comision = Comision::find($todo->id_comision);
        $alumnos_cantidad = ComisionAlumno::selectRaw('count(*) as total')
            ->where([
                'estado' => 1,
                'com_id' => $id_comision,
            ])->groupBy('com_id')->first();
        $comision->alumnos_cantidad = $alumnos_cantidad->total??0;
        $comision->save();
        return response()->json($todo,200);
    }
}

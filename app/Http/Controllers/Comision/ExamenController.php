<?php

namespace App\Http\Controllers\Comision;

use App\User;
use App\Models\Carrera;
use App\Models\CarreraModalidad;
use App\Models\Materia;
use App\Models\Inscripcion;
use App\Models\Alumno;
use App\Models\Comision;
use App\Models\ComisionAlumno;
use App\Models\TipoExamenAlumno;
use App\Models\Comision\Examen;
use App\Models\Comision\TipoExamen;
use App\Models\Comision\ExamenAlumno;
use App\Events\ComisionExamenModificado;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;

use Carbon\Carbon;

class ExamenController extends Controller
{
    public function index(Request $request)
    {
        $id_sede = $request->route('id_sede');
        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);
        
        $registros = Examen::with('tipo','comision.carrera','comision.materia','usuario')
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
        $anio = $request->query('anio',null);

        $registros = $registros
            ->when($id_departamento>0,function($q)use($id_departamento){
                $carreras = Carrera::where([
                    'dep_id' => $id_departamento,
                    'estado' => 1,
                ])->pluck('car_id')->toArray();
                return $q->whereHas('comision',function($q)use($carreras){
                    $q->whereIn('car_id',$carreras);
                });
            })
            ->when($id_carrera>0,function($q)use($id_carrera){
                return $q->whereHas('comision',function($q)use($id_carrera){
                    $q->where('car_id',$id_carrera);
                });
            })
            ->when($id_materia>0,function($q)use($id_materia){
                return $q->whereHas('comision',function($q)use($id_materia){
                    $q->where('mat_id',$id_carrera);
                });
            })
            ->when(!empty($anio) and $anio>0,function($q)use($anio){
                return $q->whereHas('comision',function($q)use($anio){
                    $q->where('com_anio',$anio);
                });
            })
            ->when($id_comision>0,function($q)use($id_comision){
                return $q->where('id_comision',$id_comision);
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
                        $query->whereRaw("DATE_FORMAT(cex_fecha, '%d/%m/%Y') like '%".$value."%'")
                        ->orWhereHas('comision',function($q)use($value){
                            $q->where('estado',1)
                            ->whereHas('materia',function($qt)use($value){
                                $qt->where('mat_nombre','like','%'.$value.'%')
                                    ->orWhere('mat_codigo','like','%'.$value.'%');
                            });
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
     * Store
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id_sede = $request->route('id_sede');
        $id_comision = $request->route('id_comision');
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'fecha' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $id_tipo_examen = $request->input('id_tipo_examen');
        $fecha = $request->input('fecha');
        $nombre = $request->input('nombre');
        $observaciones = $request->input('observaciones');
        $id_examen_virtual = $request->input('id_examen_virtual');

        $comision = Comision::find($id_comision);
        if(!$comision){
            return response()->json(['error'=>'La comision no fue encontrada.'],403);
        }

        $todo = new Examen;
        $todo->id_tipo_examen = $id_tipo_examen;
        $todo->fecha = $fecha;
        $todo->nombre = $nombre;
        $todo->observaciones = $observaciones;
        $todo->id_comision = $id_comision;
        $todo->usu_id = $user->id;
        $todo->id_examen_virtual = $id_examen_virtual;
        $todo->save();

        $asistentes = ComisionAlumno::where([
            'com_id' => $id_comision,
            'estado' => 1,
        ])
        ->get()
        ->sortBy(function($useritem, $key) {
          return $useritem->alumno->apellido;
        })
        ->pluck('alu_id')->toArray();

        foreach ($asistentes as $asistente) {
            $alumno = new ExamenAlumno;
            $alumno->id_comision_examen = $todo->id;
            $alumno->id_alumno = $asistente;
            $alumno->save();
        }

        event(new ComisionExamenModificado($todo));

        return response()->json($todo,200);
    }

    public function update(Request $request){
        $id_sede = $request->route('id_sede');
        $id_comision_examen = $request->route('id_comision_examen');
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'fecha' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $id_tipo_examen = $request->input('id_tipo_examen');
        $fecha = $request->input('fecha');
        $nombre = $request->input('nombre');
        $observaciones = $request->input('observaciones');
        $id_examen_virtual = $request->input('id_examen_virtual');

        $todo = Examen::find($id_comision_examen);
        $todo->id_tipo_examen = $id_tipo_examen;
        $todo->fecha = $fecha;
        $todo->nombre = $nombre;
        $todo->observaciones = $observaciones;
        $todo->id_examen_virtual = $id_examen_virtual;
        $todo->save();

        event(new ComisionExamenModificado($todo));

        return response()->json($todo,200);
    }

    public function alumno(Request $request){
        $id_comision_examen = $request->route('id_comision_examen');
        $id_alumno = $request->route('id_alumno');
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'id_tipo_asistencia_alumno' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $id_tipo_asistencia_alumno = $request->input('id_tipo_asistencia_alumno');
        $nota = $request->input('nota');
        $observaciones = $request->input('observaciones');

        $asistencia = ExamenAlumno::where([
            'estado' => 1,
            'cex_id' => $id_comision_examen,
            'alu_id' => $id_alumno,
        ])->first();
        if($asistencia){
            $asistencia->id_tipo_asistencia_alumno = $id_tipo_asistencia_alumno;
            $asistencia->nota = $nota;
            $asistencia->observaciones = $observaciones;
            $asistencia->save();
            return response()->json($asistencia,200);
        }

        return response()->json(['error'=>'El alumno no fue encontrado.'],403);
    }

    public function alumnos(Request $request){
        $id_comision_examen = $request->route('id_comision_examen');

        $alumnos = ExamenAlumno::with('tipo','alumno')
        ->where([
            'estado' => 1,
            'cex_id' => $id_comision_examen,
        ])
        ->get()
        ->sortBy(function($useritem, $key) {
          return $useritem->alumno->apellido;
        })->values();

        return response()->json($alumnos,200);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id_comision_examen = $request->route('id_comision_examen');
        $todo = Examen::with('tipo','usuario')->find($id_comision_examen);
        return response()->json($todo,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $id_comision_examen = $request->route('id_comision_examen');

        $todo = Examen::find($id_comision_examen);
        if($todo){
            $todo->estado = 0;
            $todo->deleted_at = Carbon::now();
            $todo->usu_id_baja = $user->id;
            $todo->save();
        }
        return response()->json($todo,200);
    }

    public function tipos(Request $request){
        $todo = TipoExamen::where('estado',1)->get();
        return response()->json($todo,200);
    }

}

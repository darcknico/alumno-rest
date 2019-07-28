<?php

namespace App\Http\Controllers\Mesa;

use App\User;
use App\Models\Mesa\MesaExamen;
use App\Models\Mesa\MesaExamenMateria;
use App\Models\Mesa\MesaExamenMateriaAlumno;
use App\Models\Mesa\TipoCondicionAlumno;
use App\Models\Carrera;
use App\Models\CarreraModalidad;
use App\Models\Materia;
use App\Models\Inscripcion;
use App\Models\Alumno;
use App\Models\Comision;
use App\Models\ComisionAlumno;
use App\Models\Asistencia;
use App\Models\Sede;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use Carbon\Carbon;
use JasperPHP\JasperPHP;

class MesaExamenMateriaAlumnoController extends Controller
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

        $id_departamento = $request->query('id_departamento',0);
        $id_carrera = $request->query('id_carrera',0);
        $id_materia = $request->query('id_materia',0);
        $id_mesa_examen = $request->query('id_mesa_examen',0);
        $id_alumno = $request->query('id_alumno',0);

        $registros = MesaExamenMateriaAlumno::with('mesa_examen_materia.materia','alumno')
            ->whereHas('mesa_examen_materia',function($q)use($id_sede){
                $q->whereHas('mesa_examen',function($qt)use($id_sede){
                    $qt->where([
                        'estado' => 1,
                        'sed_id' => $id_sede,
                    ]);
                })
                ->where('estado',1);
            })
            ->where([
            'estado' => 1,
        ]);

        $registros = $registros
            ->when($id_departamento>0,function($q)use($id_departamento){
                $carreras = Carrera::where([
                    'dep_id' => $id_departamento,
                    'estado' => 1,
                ])->pluck('car_id')->toArray();
                return $q->whereHas('mesa_examen_materia',function($qt)use($carreras){
                    $qt->whereIn('car_id',$carreras);
                });
            })
            ->when($id_carrera>0,function($q)use($id_carrera){
                return $q->whereHas('mesa_examen_materia',function($qt)use($id_carrera){
                    $qt->where('car_id',$id_carrera);
                });
            })
            ->when($id_materia>0,function($q)use($id_materia){
                return $q->whereHas('mesa_examen_materia',function($qt)use($id_materia){
                    $qt->where('mat_id',$id_carrera);
                });
            })
            ->when($id_mesa_examen>0,function($q)use($id_mesa_examen){
                return $q->where('id_mesa_examen',$id_mesa_examen);
            })
            ->when($id_alumno>0,function($q)use($id_alumno){
                return $q->whereHas('alumno',function($qt)use($id_alumno){
                    $qt->where('id_alumno',$id_alumno);
                });
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
                        $query->whereHas('usuario',function($q)use($value){
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

    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(),[
            'id_mesa_examen_materia' => 'id_mesa_examen_materia',
            'id_alumno' => 'required',
            'inscripcion' => 'required',
            'adeuda' => 'boolean | nullable',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $id_mesa_examen_materia = $request->input('id_mesa_examen_materia');
        $id_alumno = $request->input('id_alumno');
        $id_inscripcion = $request->input('id_inscripcion');
        $id_tipo_mesa_docente = $request->input('id_tipo_mesa_docente');
        $adeuda = $request->input('adeuda');

        $materia = MesaExamenMateria::find($id_mesa_examen_materia);
        if(!$materia){
            return response()->json(['error'=>'La mesa de examen no fue encontrada.'],403);
        }

        $alumno = Alumno::find($id_alumno);
        if(!$alumno){
            return response()->json(['error'=>'El alumno no existe.'],403);
        }

        $inscripcion = Inscripcion::where([
            'estado' => 1,
            'ins_id' => $id_inscripcion,
            'car_id' => $materia->id_carrera,
        ])->orderBy('created_at','desc')->first();
        if(!$inscripcion){return response()->json([
                'error'=>'No tiene una inscripcion a la carrera de la mesa de examen.',
            ],403);
        }

        $todo = MesaExamenMateriaAlumno::where([
            'estado' => 1,
            'alu_id' => $id_alumno,
            'mma_id' => $id_mesa_examen_materia,
        ])->first();
        if($todo){
            return response()->json(['error'=>'El alumno ya fue inscripto a la mesa de examen.'],403);
        } else {
            $todo = new MesaExamenMateriaAlumno;
            $todo->id_mesa_examen_materia = $id_mesa_examen_materia;
            $todo->id_alumno = $id_alumno;
            $todo->id_inscripcion = $id_inscripcion;
            $todo->nota = $request->input('nota');
            $todo->nota_nombre = $request->input('nota_nombre');
            $todo->id_tipo_condicion_alumno = $request->input('id_tipo_condicion_alumno',1);
            $todo->usu_id = $user->id;
            $todo->adeuda = $adeuda;
            $todo->save();
        }
        $alumnos_cantidad = MesaExamenMateriaAlumno::selectRaw('count(*) as total')
            ->where([
                'estado' => 1,
                'mma_id' => $id_mesa_examen_materia,
            ])->groupBy('mma_id')->first();
        $mesa_examen_materia->alumnos_cantidad = $alumnos_cantidad->total??0;
        $mesa_examen_materia->save();
        return response()->json($todo,200);
    }

    public function show(Request $request){
        $id_mesa_examen_materia_alumno = $request->route('id_mesa_examen_materia_alumno');

        $alumno = MesaExamenMateriaAlumno::with('alumno','condicion')->find($id_mesa_examen_materia_alumno);

        return response()->json($alumno,200);
    }

    public function update(Request $request){
        $id_mesa_examen_materia_alumno = $request->route('id_mesa_examen_materia_alumno');

        $validator = Validator::make($request->all(),[
            'asistencia' => 'boolean | nullable',
            'nota' => 'required',
            'nota_nombre' => 'required',
            'id_tipo_condicion_alumno' => 'required',
            'adeuda' => 'boolean | nullable',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $asistencia = $request->input('asistencia');
        $nota = $request->input('nota');
        $nota_nombre = $request->input('nota_nombre');
        $id_tipo_condicion_alumno = $request->input('id_tipo_condicion_alumno');
        $observaciones = $request->input('observaciones');
        $adeuda = $request->input('adeuda');

        $alumno = MesaExamenMateriaAlumno::with('alumno','condicion')->find($id_mesa_examen_materia_alumno);
        $alumno->asistencia = $asistencia;
        $alumno->nota = $nota;
        $alumno->nota_nombre = $nota_nombre;
        $alumno->id_tipo_condicion_alumno = $id_tipo_condicion_alumno;
        $alumno->adeuda = $adeuda;
        $alumno->save();

        $mesa_examen_materia = MesaExamenMateria::find($alumno->id_mesa_examen_materia);
        $alumnos_cantidad = MesaExamenMateriaAlumno::selectRaw('count(*) as total, SUM(IF(mam_nota<4,1,0)) as no_aprobado, SUM(IF(mam_nota>3,1,0)) as aprobado')
            ->where([
                'estado' => 1,
                'mma_id' => $alumno->id_mesa_examen_materia,
            ])
            ->whereNotNull('nota')
            ->groupBy('mma_id')->first();
        $mesa_examen_materia->alumnos_cantidad_aprobado = $alumnos_cantidad->aprobado??0;
        $mesa_examen_materia->alumnos_cantidad_no_aprobado = $alumnos_cantidad->no_aprobado??0;
        $mesa_examen_materia->save();

        return response()->json($alumno,200);
    }

/*
    public function destroy(Request $request){
        $user = Auth::user();
        $id_mesa_examen_materia_alumno = $request->route('id_mesa_examen_materia_alumno');

        $alumno = MesaExamenMateriaAlumno::with('alumno','condicion')->find($id_mesa_examen_materia_alumno);
        $alumno->estado = 0;
        $alumno->usu_id_baja = $asistencia;
        $alumno->deleted_at = Carbon::now();
        $alumno->save();

        return response()->json($alumno,200);
    }
*/
    public function reporte_constancia(Request $request){
        $id_mesa_examen_materia_alumno = $request->route('id_mesa_examen_materia_alumno');
        $alumno = MesaExamenMateriaAlumno::find($id_mesa_examen_materia_alumno);

        $jasper = new JasperPHP;
        $input = storage_path("app/reportes/alumno_mesa_inscripcion.jasper");
        $output = storage_path("app/reportes/".uniqid());
        $ext = "pdf";

        $jasper->process(
            $input,
            $output,
            [$ext],
            [
                'id_mesa_examen_materia_alumno' => $id_mesa_examen_materia_alumno,
                'logo'=> storage_path("app/images/logo_constancia.png")??null,
                'REPORT_LOCALE' => 'es_AR',
            ],
            \Config::get('database.connections.mysql')
        )->execute();
        
        $filename ='constancia_inscripcion_mesa-'.$alumno->id.$ext;
        return response()->download($output . '.' . $ext, $filename)->deleteFileAfterSend();
    }
}
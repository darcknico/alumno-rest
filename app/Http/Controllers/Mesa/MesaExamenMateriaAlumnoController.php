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

use App\Functions\MesaExamenFunction;

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
            'id_mesa_examen_materia' => 'required | integer',
            'id_alumno' => 'required | integer',
            'id_inscripcion' => 'required | integer',
            'adeuda' => 'boolean | nullable',
            'nota' => 'nullable | integer',
            'id_tipo_condicion_alumno' => 'nullable | integer',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $id_mesa_examen_materia = $request->input('id_mesa_examen_materia');
        $id_alumno = $request->input('id_alumno');
        $id_inscripcion = $request->input('id_inscripcion');
        $adeuda = $request->input('adeuda');
        $observaciones = $request->input('observaciones');

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
            $f = new \NumberFormatter("es", \NumberFormatter::SPELLOUT);

            $id_tipo_condicion_alumno = $request->input('id_tipo_condicion_alumno',1);
            $nota = $request->input('nota');

            $todo = new MesaExamenMateriaAlumno;
            $todo->id_mesa_examen_materia = $id_mesa_examen_materia;
            $todo->id_alumno = $id_alumno;
            $todo->id_inscripcion = $id_inscripcion;
            $todo->nota = $nota;
            if(!is_null($nota)){
                $todo->nota_nombre = $f->format($nota);
            }
            $todo->id_tipo_condicion_alumno = $id_tipo_condicion_alumno;
            if($id_tipo_condicion_alumno == 2){
                $todo->nota_final = $nota;
                $todo->nota_final_nombre = $f->format($nota);
            }
            $todo->usu_id = $user->id;
            $todo->adeuda = $adeuda;
            $todo->observaciones = $observaciones;
            $todo->save();
        }
        MesaExamenFunction::actualizar_materia($materia);
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
            'nota' => 'nullable | integer',
            'nota_nombre' => 'nullable',
            'nota_final' => 'nullable | integer',
            'id_tipo_condicion_alumno' => 'required',
            'adeuda' => 'boolean | nullable',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $f = new \NumberFormatter("es", \NumberFormatter::SPELLOUT);

        $asistencia = $request->input('asistencia');
        $nota = $request->input('nota');
        $nota_nombre = $request->input('nota_nombre',null);
        $nota_final = $request->input('nota_final');
        $nota_final_nombre = $request->input('nota_final_nombre',null);
        $id_tipo_condicion_alumno = $request->input('id_tipo_condicion_alumno');
        $observaciones = $request->input('observaciones');
        $adeuda = $request->input('adeuda');

        $alumno = MesaExamenMateriaAlumno::find($id_mesa_examen_materia_alumno);
        $alumno->asistencia = $asistencia;
        $alumno->nota = $nota;
        if(!is_null($nota)){
            $alumno->nota_nombre = $f->format($nota);
        } else {
            $alumno->nota_nombre = null;
        }
        
        $alumno->nota_final = $nota_final;
        if(!is_null($nota_final)){
            $alumno->nota_final_nombre = $f->format($nota_final);
        } else {
            $alumno->nota_final_nombre = null;
        }
        $alumno->id_tipo_condicion_alumno = $id_tipo_condicion_alumno;
        $alumno->adeuda = $adeuda;
        $alumno->observaciones = $observaciones;
        $alumno->save();

        $mesa_examen_materia = MesaExamenMateria::find($alumno->id_mesa_examen_materia);
        MesaExamenFunction::actualizar_materia($mesa_examen_materia);
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

    public function reporte_constancia_asistencia(Request $request){
        $id_sede = $request->route('id_sede');
        $id_mesa_examen_materia_alumno = $request->route('id_mesa_examen_materia_alumno');
        $alumno = MesaExamenMateriaAlumno::find($id_mesa_examen_materia_alumno);
        $id_inscripcion = $alumno->id_inscripcion;
        $id_materia = $alumno->mesa_examen_materia->id_materia;
        $fecha = Carbon::parse($alumno->mesa_examen_materia->fecha)->toDateString();
        $tipo = "mesa";


        $jasper = new JasperPHP;
        $input = storage_path("app/reportes/alumno_constancia_general.jasper");
        $output = storage_path("app/reportes/".uniqid());
        $ext = "pdf";

        $jasper->process(
            $input,
            $output,
            [$ext],
            [
                'id_materia' => $id_materia,
                'id_inscripcion' => $id_inscripcion,
                'id_sede' => $id_sede,
                'fecha' => $fecha,
                'tipo' => $tipo,
                'logo'=> storage_path("app/images/logo_constancia.png")??null,
                'REPORT_LOCALE' => 'es_AR',
            ],
            \Config::get('database.connections.mysql')
        )->execute();
        
        $filename ='constancia_asistencia_mesa-'.$alumno->id.$ext;
        return response()->download($output . '.' . $ext, $filename)->deleteFileAfterSend();
    }
}
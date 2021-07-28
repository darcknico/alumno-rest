<?php

namespace App\Http\Controllers\Mesa;

use App\User;
use App\Models\Mesa\MesaExamen;
use App\Models\Mesa\MesaExamenMateria;
use App\Models\PlanEstudio;
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
use App\Events\MesaExamenMateriaModificado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use Carbon\Carbon;
use JasperPHP\JasperPHP;

class MesaExamenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $id_sede = $request->route('id_sede');
        $registros = MesaExamen::with('sede','usuario')
            ->where([
            'estado' => 1,
            'sed_id' => $id_sede,
            ]);

        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);

        $anio = $request->query('anio',0);

        $registros = $registros
            ->when($anio>0,function($q)use($anio){
                $q->whereYear('fecha_inicio','=',$anio);
            });

        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros->orderBy('fecha_inicio','desc')
            ->get();
            return response()->json($todo,200);
        }

        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
                if(strlen($value)>0){
                    $registros = $registros->where(function($query) use  ($value) {
                        $query->where('numero', $value)
                            ->orWhere('nombre','like','%'.$value.'%');
                    });
                }
            }
        }
        if(strlen($sort)>0){
            $registros = $registros->orderBy($sort,$order);
        } else {
            $registros = $registros->orderBy('fecha_inicio','desc');
        }
        $sql = $registros->toSql();
        $q = clone($registros->getQuery());
        $total_count = $q->groupBy('sed_id')->count();
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
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'fecha_inicio' => 'required',
            'notificacion_push' => 'nullable | boolean',
            'notificacion_email' => 'nullable | boolean',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],404);
        }
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin',$fecha_inicio);
        if(empty($fecha_fin)){
            $fecha_fin = $fecha_inicio;
        }
        $nombre = $request->input('nombre');
        $notificacion_push = $request->input('notificacion_push',false);
        $notificacion_email = $request->input('notificacion_email',false);

        $sede = Sede::find($id_sede);
        $numero = $sede->mesa_numero + 1;
        $todo = new MesaExamen;
        $todo->numero = $numero;
        $todo->nombre = $nombre;
        $todo->notificacion_push = $notificacion_push;
        $todo->notificacion_email = $notificacion_email;
        $todo->fecha_inicio = $fecha_inicio;
        $todo->fecha_fin = $fecha_fin;
        $todo->id_sede = $id_sede;
        $todo->usu_id= $user->id;
        $todo->save();
        $sede->mesa_numero = $numero;
        $sede->save();

        return response()->json($todo,200);
    }

    public function materia_asociar(Request $request){
        $user = Auth::user();
        $id_sede = $request->route('id_sede');
        $id_mesa_examen = $request->route('id_mesa_examen');
        $id_materia = $request->route('id_materia');

        $validator = Validator::make($request->all(),[
            'fecha' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],404);
        }

        $fecha = $request->input('fecha');
        $ubicacion = $request->input('ubicacion',null);
        $mesa_examen = MesaExamen::find($id_mesa_examen);
        $materia = Materia::find($id_materia);
        if($mesa_examen and $materia){
            $todo = MesaExamenMateria::where([
                'estado' => 1,
                'mat_id' => $id_materia,
                'mes_id' => $id_mesa_examen,
            ])->first();
            if($todo){
                response()->json([
                    'error'=>'La materia ya cuenta con mesa de examen.',
                ],404);
            }
            $todo = new MesaExamenMateria;
            $todo->id_mesa_examen = $id_mesa_examen;
            $todo->id_carrera = $materia->planEstudio->id_carrera;
            $todo->id_materia = $id_materia;
            $todo->usu_id = $user->id;
            $todo->fecha = $fecha;
            $todo->ubicacion = $ubicacion;
            $todo->save();
            
            event(new MesaExamenMateriaModificado($todo));

            return response()->json($todo,200);
        }
        return response()->json([
            'error'=>'No se han encontrado la Materia o la Mesa de examen.',
        ],404);
    }

    public function materia_desasociar(Request $request){
        $user = Auth::user();
        $id_mesa_examen = $request->route('id_mesa_examen');
        $id_materia = $request->route('id_materia');

        $mesa_examen = MesaExamen::find($id_mesa_examen);
        $materia = Materia::find($id_materia);
        if($mesa_examen and $materia){
            $todo = MesaExamenMateria::where([
                'estado' => 1,
                'mat_id' => $id_materia,
                'mes_id' => $id_mesa_examen,
            ])->first();
            if($todo){
                $todo->estado = 0;
                $todo->usu_id_baja = $user->id;
                $todo->deleted_at = Carbon::now();
                $todo->save();
            }
            return response()->json($todo,200);
        }
        return response()->json([
            'error'=>'No se han encontrado la Materia o la Mesa de examen.',
        ],404);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id_mesa_examen = $request->route('id_mesa_examen');
        $todo = MesaExamen::with([
            'usuario',
            'sede',
        ])->find($id_mesa_examen);
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
        $id_mesa_examen = $request->route('id_mesa_examen');
        $validator = Validator::make($request->all(),[
            'fecha_fin' => 'required',
            'notificacion_push' => 'nullable | boolean',
            'notificacion_email' => 'nullable | boolean',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],404);
        }
        $fecha_fin = $request->input('fecha_fin');
        $nombre = $request->input('nombre');
        $notificacion_push = $request->input('notificacion_push',false);
        $notificacion_email = $request->input('notificacion_email',false);

        $todo = MesaExamen::find($id_mesa_examen);
        $todo->nombre = $nombre;
        $todo->fecha_fin = $fecha_fin;
        $todo->notificacion_push = $notificacion_push;
        $todo->notificacion_email = $notificacion_email;
        $todo->save();
        return response()->json($todo,200);
    }

    public function materias(Request $request){
        $id_mesa_examen = $request->route('id_mesa_examen');
        $id_carrera = $request->query('id_carrera',0);

        $todo = MesaExamenMateria::with('materia.planEstudio','carrera','usuario')
        ->when($id_carrera>0,function($q)use($id_carrera){
          return $q->where('id_carrera',$id_carrera);
        })
        ->where([
            'estado' => 1,
            'mes_id' => $id_mesa_examen,
        ])->orderBy('fecha','asc')->get();
        return response()->json($todo,200);
    }

    public function materias_disponibles_comision(Request $request){
        $id_sede = $request->route('id_sede');
        $id_mesa_examen = $request->route('id_mesa_examen');
        $fecha = Carbon::now();
        $year = $fecha->year - 1;
        $materias = Comision::where([
            'estado' => 1,
            'sed_id' => $id_sede,
        ])->where('anio','>=',$year)
        ->pluck('mat_id')->toArray();
        $mesas = MesaExamenMateria::where([
            'estado' => 1,
            'mes_id' => $id_mesa_examen,
        ])->pluck('mat_id')->toArray();
        $todo = Materia::with('planEstudio.carrera')
        ->where([
            'estado' => 1,
        ])
        ->whereIn('mat_id',$materias)
        ->whereNotIn('mat_id',$mesas)
        ->orderBy('codigo','desc')
        ->get();
        return response()->json($todo,200);
    }

    public function materias_disponibles(Request $request){
        $id_sede = $request->route('id_sede');
        $id_mesa_examen = $request->route('id_mesa_examen');

        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);
        $id_carrera = $request->query('id_carrera',0);

        $registros = Materia::with('planEstudio.carrera')
        ->whereDoesntHave('mesas_examenes',function($q)use($id_mesa_examen){
            $q->where('id_mesa_examen',$id_mesa_examen)->where('estado',1);
        })
        ->where([
            'estado' => 1,
        ])
        ->when($id_carrera>0,function($q)use($id_carrera){
            $q->where('planEstudio',function($qt)use($id_carrera){
                $qt->where('id_carrera',$id_carrera)->where('estado',1);
            });
        });
        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $registros = $registros->orderBy('created_at','desc')
            ->get();
            return response()->json($registros,200);
        }
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
                if(strlen($value)>0){
                    $registros = $registros->where(function($query) use  ($value) {
                        $query->where('mat_nombre','like','%'.$value.'%')
                            ->orWhere('mat_codigo','like','%'.$value.'%')
                            ->orWhere('mat_horas',$value);
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $id_mesa_examen = $request->route('id_mesa_examen');

        $todo = MesaExamen::find($id_mesa_examen);
        if($todo){
            $todo->estado = 0;
            $todo->deleted_at = Carbon::now();
            $todo->usu_id_baja = $user->id;
            $todo->save();
        }
        return response()->json($todo,200);
    }

    public function reporte_resumen(Request $request){
        $id_mesa_examen = $request->route('id_mesa_examen');
        $mesa = MesaExamen::find($id_mesa_examen);

        $jasper = new JasperPHP;
        $input = storage_path("app/reportes/alumno_mesa_examen.jasper");
        $output = storage_path("app/reportes/".uniqid());
        $ext = "pdf";

        $jasper->process(
            $input,
            $output,
            [$ext],
            [
                'id_mesa_examen' => $id_mesa_examen,
                'logo'=> storage_path("app/images/logo_constancia.png")??null,
                'REPORT_LOCALE' => 'es_AR',
            ],
            \Config::get('database.connections.mysql')
        )->execute();
        
        $filename ='mesa_examen-'.$mesa->numero.$ext;
        return response()->download($output . '.' . $ext, $filename)->deleteFileAfterSend();
    }

    public function materia_masivo_previa(Request $request){
        $id_sede = $request->route('id_sede');
        $id_mesa_examen = $request->route('id_mesa_examen',0);

        $id_departamento = $request->query('id_departamento',0);
        $id_carrera = $request->query('id_carrera',0);

        $mesa_examen = MesaExamen::find($id_mesa_examen);

        $carreras = Carrera::where([
            'estado' => 1,
        ])
        ->when($id_departamento>0,function($q)use($id_departamento){
            $q->where('id_departamento',$id_departamento);
        })
        ->when($id_carrera>0,function($q)use($id_carrera){
            $q->where('id',$id_carrera);
        })
        ->whereNotNull('id_plan_estudio')
        ->get()
        ->pluck('id_plan_estudio');
        /*
        $existentes = MesaExamenMateria::where([
            'estado' => 1,
        ])
        ->where('id_mesa_examen',$id_mesa_examen)
        ->get()
        ->pluck('id_materia');
        */
        $materias = Materia::selectRaw('count(*) as total')
        ->where('estado',1)
        ->whereHas('planEstudio',function($q)use($id_departamento,$id_carrera){
            $q->where('estado',1)
            ->when($id_carrera>0,function($qt)use($id_carrera){
                $qt->where('id_carrera',$id_carrera);
            })
            ->whereHas('carrera',function($qt)use($id_departamento){
                $qt->where('estado',1)
                ->when($id_departamento>0,function($qtr)use($id_departamento){
                    $qtr->where('id_departamento',$id_departamento);
                });
            });
        })
        ->whereDoesntHave('mesas_examenes',function($q)use($id_mesa_examen){
            $q->where('estado',1)
            ->where('id_mesa_examen',$id_mesa_examen);
        })
        #->whereIn('id_plan_estudio',$carreras)
        #->whereNotIn('id',$existentes)
        ->groupBy('estado')
        ->first();

        return response()->json([
            'total_carreras'=>count($carreras),
            'total_materias'=>$materias->total??0,
        ],200);
    }

    public function materia_masivo_asociar(Request $request){
        $user = Auth::user();
        $id_sede = $request->route('id_sede');
        $id_mesa_examen = $request->route('id_mesa_examen');

        $validator = Validator::make($request->all(),[
            'id_departamento' => 'required | integer',
            'id_carrera' => 'required  | integer',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],404);
        }

        $id_departamento = $request->input('id_departamento');
        $id_carrera = $request->input('id_carrera');

        $mesa_examen = MesaExamen::find($id_mesa_examen);

        $carreras = Carrera::where([
            'estado' => 1,
        ])
        ->when($id_departamento>0,function($q)use($id_departamento){
            $q->where('id_departamento',$id_departamento);
        })
        ->when($id_carrera>0,function($q)use($id_carrera){
            $q->where('id',$id_carrera);
        })
        ->whereNotNull('id_plan_estudio')
        ->get()
        ->pluck('id_plan_estudio');
        $existentes = MesaExamenMateria::where([
            'estado' => 1,
        ])
        ->where('id_mesa_examen',$id_mesa_examen)
        ->get()
        ->pluck('id_materia');

        $materias = Materia::where('estado',1)
        ->whereIn('id_plan_estudio',$carreras)
        ->whereNotIn('id',$existentes)
        ->get();

        $fecha = Carbon::parse($mesa_examen->mes_fecha_inicio);
        foreach ($materias as $materia) {
            $todo = new MesaExamenMateria;
            $todo->id_mesa_examen = $id_mesa_examen;
            $todo->id_carrera = $materia->planEstudio->id_carrera;
            $todo->id_materia = $materia->id;
            $todo->usu_id = $user->id;
            $todo->fecha = $fecha;
            $todo->save();
            event(new MesaExamenMateriaModificado($todo));
        }
        return response()->json($materias,200);
    }

}

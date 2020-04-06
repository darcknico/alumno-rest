<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Carrera;
use App\Models\CarreraModalidad;
use App\Models\Materia;
use App\Models\Inscripcion;
use App\Models\Alumno;
use App\Models\Comision;
use App\Models\ComisionAlumno;
use App\Models\Comision\Docente as ComisionDocente;
use App\Models\Asistencia;
use App\Models\Comision\Examen;
use App\Models\Academico\DocenteMateria;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use Carbon\Carbon;
use JasperPHP\JasperPHP; 

class ComisionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $id_sede = $request->route('id_sede');
        $id_carrera = $request->route('id_carrera',null);
        $id_materia = $request->route('id_materia',null);
        $registros = Comision::with('sede','carrera','materia.planEstudio','modalidad')
            ->where([
            'estado' => 1,
            'sed_id' => $id_sede,

            ]);
        if(!is_null($id_carrera)){
            $registros = $registros->where('id_carrera',$id_carrera);
        } else if (!is_null($id_materia)){
            $registros = $registros->where('id_materia',$id_materia);
        }

        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);
        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros->orderBy('created_at','desc')
            ->get();
            return response()->json($todo,200);
        }

        $id_departamento = $request->query('id_departamento',0);
        $id_carrera = $request->query('id_carrera',0);
        $id_materia = $request->query('id_materia',0);
        $anio = $request->query('anio',null);
        $cerrado = $request->query('cerrado',null);
        $id_usuario = $request->query('id_usuario',0);

        $registros = $registros
            ->when($id_departamento>0,function($q)use($id_departamento){
                $carreras = Carrera::where([
                    'dep_id' => $id_departamento,
                    'estado' => 1,
                ])->pluck('car_id')->toArray();
                return $q->whereIn('car_id',$carreras);
            })
            ->when($id_carrera>0,function($q)use($id_carrera){
                return $q->where('car_id',$id_carrera);
            })
            ->when($id_materia>0,function($q)use($id_materia){
                return $q->where('mat_id',$id_materia);
            })
            ->when(!empty($anio),function($q)use($anio){
                return $q->where('anio',$anio);
            })
            ->when(!empty($cerrado),function($q)use($cerrado){
                return $q->where('cerrado',$cerrado);
            })
            ->when($user->id_tipo_usuario == 8,function($q)use($user){
                $q->whereHas('docentes',function($qt)use($user){
                    $qt->where('id_usuario',$user->id)->where('estado',1);
                });
            })
            ->when($id_usuario>0,function($q)use($id_usuario){
                $q->whereHas('docentes',function($qt)use($id_usuario){
                    $qt->where('id_usuario',$id_usuario)->where('estado',1);
                });
            });
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
                if(strlen($value)>0){
                    $registros = $registros->where(function($query) use  ($value) {
                        $query->where('anio', $value)
                            ->orWhere('numero','like','%'.$value.'%')
                            ->orWhereIn('car_id',function($q)use($value){
                                return $q->select('car_id')->from('tbl_carreras')->where([
                                    'estado' => 1,
                                ])->where(function($qt) use ($value) {
                                    $qt->where('car_nombre','like','%'.$value.'%')->orWhere('car_nombre_corto','like','%'.$value.'%');
                                });
                            })
                            ->orWhereIn('mat_id',function($q)use($value){
                                return $q->select('mat_id')->from('tbl_materias')->where([
                                    'estado' => 1,
                                ])->where(function($qt) use ($value) {
                                    $qt->where('mat_nombre','like','%'.$value.'%')->orWhere('mat_codigo','like','%'.$value.'%');
                                });
                            });
                    });
                }
            }
        }
        if(strlen($sort)>0){
            $registros = $registros->orderBy($sort,$order);
        } else {
            $registros = $registros->orderBy('created_at','desc');
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

    public function carreras(Request $request){
        $id_carrera = $request->route('id_carrera');
        $id_inscripcion = $request->query('id_inscripcion',0);
        $anio = $request->query('anio',0);

        $registros = Comision::with('sede','carrera','materia.planEstudio')
            ->when($anio>0,function($q)use($anio){
                $q->where('anio',$anio);
            })
            ->when($id_inscripcion>0,function($q)use($id_inscripcion){
                $q->whereDoesntHave('alumnos',function($qt)use($id_inscripcion){
                    $qt->where([
                        'estado' => 1,
                        'ins_id' => $id_inscripcion,
                    ]);
                });
            })
            ->where([
                'estado' => 1,
                'car_id' => $id_carrera,
            ])
            ->orderBy('anio','desc')
            ->get();
        $salida = [];
        foreach ($registros as $registro) {
            if($id_inscripcion>0){
                $registro->inscripcion_ultima = ComisionAlumno::with('comision')
                ->whereHas('comision',function($q)use($registro){
                    $q->where('id_materia',$registro->id_materia)
                    ->where('estado',1);
                })
                ->where('id_inscripcion',$id_inscripcion)
                ->where('estado',1)
                ->orderBy('created_at','desc')
                ->first();
            }
            $salida[]=$registro;
        }
        return response()->json($salida,200);
    }

    public function materias(Request $request){
        $id_materia = $request->route('id_materia');
        $registros = Comision::with('sede','carrera','materia.planEstudio')
            ->where([
                'estado' => 1,
                'mat_id' => $id_materia,
            ])->get();
        return response()->json($todo,200);
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
            'anio' => 'required',
            'numero' => 'required',
            'id_materia' => 'required',
            'id_usuario' => 'required',
            'responsable_nombre' => 'required',
            'responsable_apellido' => 'required',
            'clase_inicio' => 'date | nullable',
            'clase_final' => 'date | nullable',
            'asistencia' => 'boolean | nullable',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],404);
        }
        $anio = $request->input('anio');
        $numero = $request->input('numero');
        $id_materia = $request->input('id_materia');
        $id_usuario = $request->input('id_usuario');
        $responsable_nombre = $request->input('responsable_nombre');
        $responsable_apellido = $request->input('responsable_apellido');
        $id_modalidad = $request->input('id_modalidad',1);
        $clase_inicio = $request->input('clase_inicio');
        $clase_final = $request->input('clase_final');
        $asistencia = $request->input('asistencia',false);
        $docentes = $request->input('docentes',[]);
        if(is_null($docentes)){
            $docentes = [];
        }
        $materia = Materia::find($id_materia);
        if(!$materia){
            return response()->json(['error'=>'La materia no fue encontrada.'],404);
        }

        $todo = new Comision;
        $todo->anio = $anio;
        $todo->numero = $numero;
        $todo->id_materia = $id_materia;
        $todo->id_carrera = $materia->planEstudio->id_carrera;
        $todo->id_usuario = $id_usuario;
        $todo->responsable_nombre = $responsable_nombre;
        $todo->responsable_apellido = $responsable_apellido;
        $todo->id_modalidad = $id_modalidad;
        $todo->id_sede = $id_sede;
        $todo->clase_inicio = $clase_inicio;
        $todo->clase_final = $clase_final;
        $todo->asistencia = $asistencia;
        $todo->usu_id_alta = $user->id;
        $todo->save();

        foreach ($docentes as $docente) {
            $usuario = new ComisionDocente;
            $usuario->id_usuario = $docente['id_usuario'];
            $usuario->id_comision = $todo->id;
            $usuario->save();
        }

        return response()->json($todo,200);
    }

    public function alumno_asociar(Request $request){
        $user = Auth::user();
        $id_sede = $request->route('id_sede');
        $id_comision = $request->route('id_comision');
        $id_alumno = $request->route('id_alumno');

        $id_inscripcion = $request->input('id_inscripcion',null);
        $comision = Comision::find($id_comision);
        $alumno = Alumno::find($id_alumno);
        if($comision and $alumno){
            if(empty($id_inscripcion)){
                $inscripcion = Inscripcion::where([
                    'estado' => 1,
                    'sed_id' => $id_sede,
                    'alu_id' => $id_alumno,
                    'car_id' => $comision->id_carrera,
                ])->orderBy('created_at','desc')->first();
                if($inscripcion){
                    $id_inscripcion = $inscripcion->id;
                } else {
                    return response()->json([
                        'error'=>'No tiene una inscripcion a la carrera de la comision en la sede.',
                    ],404);
                }
            } else {
                $inscripcion = Inscripcion::find($id_inscripcion);
                if(!$inscripcion){
                    return response()->json([
                        'error'=>'La inscripcion no existe.',
                    ],404);
                }
            }
            $todo = ComisionAlumno::where([
                'estado' => 1,
                'alu_id' => $id_alumno,
                'com_id' => $id_comision,
            ])->first();
            if($todo){
                $todo->usu_id = $user->id;
                $todo->save();
            } else {
                $todo = new ComisionAlumno;
                $todo->id_comision = $id_comision;
                $todo->id_alumno = $id_alumno;
                $todo->id_inscripcion = $id_inscripcion;
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
        return response()->json([
            'error'=>'No se han encontrado la Comision o el Alumno.',
        ],404);
    }

    public function alumno_desasociar(Request $request){
        $user = Auth::user();
        $id_comision = $request->route('id_comision');
        $id_alumno = $request->route('id_alumno');

        $comision = Comision::find($id_comision);
        $alumno = Alumno::find($id_alumno);
        if($comision and $alumno){
            $todo = ComisionAlumno::where([
                'estado' => 1,
                'alu_id' => $id_alumno,
                'com_id' => $id_comision,
            ])->first();
            if($todo){
                $todo->estado = 0;
                $todo->usu_id_baja = $user->id;
                $todo->deleted_at = Carbon::now();
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
        return response()->json([
            'error'=>'No se han encontrado la Comision o el Alumno',
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
        $id_comision = $request->route('id_comision');
        $todo = Comision::with([
            'responsable',
            'carrera',
            'materia.planEstudio',
            'sede',
        ])->find($id_comision);
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
        $id_comision = $request->route('id_comision');
        $validator = Validator::make($request->all(),[
            'anio' => 'required',
            'numero' => 'required',
            'id_usuario' => 'required',
            'responsable_nombre' => 'required',
            'responsable_apellido' => 'required',
            'clase_inicio' => 'date | nullable',
            'clase_final' => 'date | nullable',
            'asistencia' => 'boolean | nullable',
            'cerrado' => 'boolean | nullable',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],404);
        }
        $anio = $request->input('anio');
        $numero = $request->input('numero');
        $id_usuario = $request->input('id_usuario');
        $responsable_nombre = $request->input('responsable_nombre');
        $responsable_apellido = $request->input('responsable_apellido');
        $id_modalidad = $request->input('id_modalidad',1);
        $cerrado = $request->input('cerrado',false);
        $clase_inicio = $request->input('clase_inicio');
        $clase_final = $request->input('clase_final');
        $asistencia = $request->input('asistencia',false);
        $docentes = $request->input('docentes');

        $todo = Comision::find($id_comision);
        $todo->anio = $anio;
        $todo->numero = $numero;
        $todo->id_usuario = $id_usuario;
        $todo->responsable_nombre = $responsable_nombre;
        $todo->responsable_apellido = $responsable_apellido;
        $todo->id_modalidad = $id_modalidad;
        $todo->cerrado = $cerrado;
        $todo->clase_inicio = $clase_inicio;
        $todo->clase_final = $clase_final;
        $todo->asistencia = $asistencia;
        $todo->save();

        $docentes_old = ComisionDocente::where('id_comision',$id_comision)->where('estado',1)->get()->toArray();
        foreach ($docentes_old as $docente) {
            $encontro = array_search($docente['id_usuario'], array_column($docentes, 'id_usuario'));
            if(!$encontro){
                $docente = ComisionDocente::find($docente['id']);
                $docente->estado=0;
                $docente->save();
            }
        }
        foreach ($docentes as $docente) {
            $encontro = array_search($docente['id_usuario'], array_column($docentes_old, 'id_usuario'));
            if(!$encontro){
                $usuario = new ComisionDocente;
                $usuario->id_usuario = $docente['id_usuario'];
                $usuario->id_comision = $id_comision;
                $usuario->save();
            }
        }
        return response()->json($todo,200);
    }

    public function alumnos(Request $request){
        $id_comision = $request->route('id_comision');
        $todo = ComisionAlumno::with('alumno.tipoDocumento')->where([
            'estado' => 1,
            'com_id' => $id_comision,
        ])->get()->sortBy(function ($batch) { 
            return $batch->alumno->apellido; 
        });
        return response()->json($todo->values()->all(),200);
    }

    public function docentes(Request $request){
        $id_comision = $request->route('id_comision');
        $todo = ComisionDocente::with('docente')->where([
            'estado' => 1,
            'com_id' => $id_comision,
        ])->get()->sortBy(function ($batch) { 
            return $batch->usuario->apellido; 
        });
        return response()->json($todo->values()->all(),200);
    }

    public function alumnos_disponibles(Request $request){
        $id_sede = $request->route('id_sede');
        $id_comision = $request->route('id_comision');

        $comision = Comision::find($id_comision);
        $alumnos = ComisionAlumno::where([
            'estado' => 1,
            'com_id' => $id_comision,
        ])->pluck('alu_id')->toArray();
        $inscripciones = Inscripcion::where([
            'estado' => 1,
            'sed_id' => $id_sede,
            'car_id' => $comision->id_carrera,
            'tie_id' => 1
        ])
        ->whereNotIn('alu_id',$alumnos)
        ->pluck('alu_id')->toArray();
        $todo = Alumno::whereIn('alu_id',$inscripciones)->orderBy('alu_apellido','asc')->get();
        return response()->json($todo,200);
    }

    public function asistencias(Request $request){
        $id_comision = $request->route('id_comision');
        $todo = Asistencia::with('usuario','usuario_baja','usuario_check_in','usuario_check_out')->where([
            'estado' => 1,
            'com_id' => $id_comision,
        ])->orderBy('fecha','desc')->get();
        return response()->json($todo,200);
    }

    public function examenes(Request $request){
        $id_comision = $request->route('id_comision');

        $todos = Examen::with('tipo','usuario')
        ->where([
            'estado' => 1,
            'com_id' => $id_comision,
        ])->orderBy('fecha','asc')->get();

        return response()->json($todos,200);
    }

    public function materia_masivo_previa(Request $request){
        $id_sede = $request->route('id_sede');

        $anio = $request->query('anio',0);
        $anio_previo = $anio - 1;

        $id_departamento = $request->query('id_departamento',0);
        $id_carrera = $request->query('id_carrera',0);

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

        $existentes = Comision::selectRaw('count(*) as total')
        ->where('estado',1)
        ->where('anio',$anio)
        ->whereHas('materia',function($q)use($carreras){
            $q->where('estado',1)
                ->whereIn('id_plan_estudio',$carreras);
        })
        ->groupBy('estado')
        ->first();
        $previos = Comision::selectRaw('count(*) as total')
        ->where('estado',1)
        ->where('anio',$anio_previo)
        ->whereHas('materia',function($q)use($carreras){
            $q->where('estado',1)
                ->whereIn('id_plan_estudio',$carreras);
        })
        ->groupBy('estado')
        ->first();

        $materias = Materia::selectRaw('count(*) as total')
        ->where('estado',1)
        ->whereIn('id_plan_estudio',$carreras)
        ->groupBy('estado')
        ->first();

        return response()->json([
            'total_carreras'=>count($carreras),
            'total_materias'=>$materias->total??0,
            'total_existentes'=>$existentes->total??0,
            'total_previos'=>$previos->total??0,
        ],200);
    }

    public function materia_masivo_asociar(Request $request){
        $user = Auth::user();
        $id_sede = $request->route('id_sede');

        $validator = Validator::make($request->all(),[
            'id_departamento' => 'required | integer',
            'id_carrera' => 'required  | integer',
            'anio' => 'required  | integer',
            'docentes_previos' => 'nullable  | boolean',
            'docentes_asignados' => 'nullable  | boolean',
            'horarios_previos' => 'nullable  | boolean',
            'asistencia' => 'nullable  | boolean',
            'clase_inicio' => 'nullable  | boolean',
            'clase_final' => 'nullable  | boolean',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],404);
        }

        $id_departamento = $request->input('id_departamento');
        $id_carrera = $request->input('id_carrera');
        $anio = $request->input('anio');
        $docentes_previos = $request->input('docentes_previos',false);
        $docentes_asignados = $request->input('docentes_asignados',false);
        $horarios_previos = $request->input('horarios_previos',false);
        $asistencia = $request->input('asistencia',false);
        $clase_inicio = $request->input('clase_inicio',false);
        $clase_final = $request->input('clase_final',false);

        $materias = Materia::where('estado',1)
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
        ->get();

        $fecha = Carbon::parse($mesa_examen->mes_fecha_inicio);
        foreach ($materias as $materia) {
            $previo = Comision::where('estado',1)
            ->where('anio',$anio_previo)
            ->where('id_materia',$materia->id)
            ->orderBy('created_at','desc')
            ->first();
            $existente = Comision::where('estado',1)
            ->where('anio',$anio)
            ->where('id_materia',$materia->id)
            ->orderBy('created_at','desc')
            ->first();
            $numero = 1;
            if($existente){
                $numero = $existente->numero + 1;
            }

            $comision = new Comision;
            $comision->anio = $anio;
            $comision->numero = $numero;
            $comision->id_materia = $materia->id;
            $comision->id_carrera = $materia->planEstudio->id_carrera;
            $comision->id_usuario = $id_usuario;
            $comision->responsable_nombre = $responsable_nombre;
            $comision->responsable_apellido = $responsable_apellido;
            $comision->id_modalidad = $id_modalidad;
            $comision->id_sede = $id_sede;
            $comision->clase_inicio = $clase_inicio;
            $comision->clase_final = $clase_final;
            $comision->asistencia = $asistencia;
            $comision->usu_id_alta = $user->id;
            $comision->save();

            if($docentes_previos and $previo){
                foreach ($previo->docentes as $docente) {
                    if($docente->estado){
                        $usuario = new ComisionDocente;
                        $usuario->id_usuario = $docente->id_usuario;
                        $usuario->id_comision = $comision->id;
                        $usuario->save();
                    }
                }
            }
            if($docentes_asignados){
                $docentes = DocenteMateria::where('estado',1)
                ->where('id_sede',$id_sede)
                ->where('id_materia',$id_materia)
                ->get();
                foreach ($docentes as $docente) {
                    if($docente->estado){
                        $existeDocente = Comision::where('estado',1)
                        ->where('id_usuario',$docente->id_usuario)
                        ->where('id_comision',$comision->id)
                        ->first();
                        if(!$existeDocente){
                            $usuario = new ComisionDocente;
                            $usuario->id_usuario = $docente->id_usuario;
                            $usuario->id_comision = $comision->id;
                            $usuario->save();
                        }
                    }
                }
            }

            if($horarios_previos and $previo){
                foreach ($previo->horarios as $horario) {
                    if($horario->estado){
                        $nuevo = $horario->replicate();
                        $nuevo->id = null;
                        $nuevo->id_comision = $comision->id;
                        $nuevo->created_at = null;
                        $nuevo->update = null;
                        $nuevo->save();
                    }
                }
            }
        }
        return response()->json($materias,200);
    }
    /*
    public function inscripcion(Request $request){
        $id_inscripcion = $request->route('id_inscripcion');
        $todo = ComisionAlumno::with('comision.carrera','comision.materia')
            ->where([
                'estado' => 1,
                'ins_id' => $id_inscripcion,
            ])->get();
        return response()->json($todo,200);
    }*/

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $id_comision = $request->route('id_comision');

        $todo = Comision::find($id_comision);
        if($todo){
            $todo->estado = 0;
            $todo->deleted_at = Carbon::now();
            $todo->usu_id_baja = $user->id;
            $todo->save();
        }
        return response()->json($todo,200);
    }

    public function reporte(Request $request){
        $id_sede = $request->route('id_sede');
        $id_comision = $request->route('id_comision');
        $comision = Comision::find($id_comision);

        $jasper = new JasperPHP;
        $input = storage_path("app/reportes/alumno_comision_asistencia.jasper");
        $output = storage_path("app/reportes/".uniqid());
        $ext = "pdf";

        $jasper->process(
            $input,
            $output,
            [$ext],
            [
                'id_comision' => $id_comision,
                'logo'=> storage_path("app/images/logo_2.png")??null,
                'REPORT_LOCALE' => 'es_AR',
            ],
            \Config::get('database.connections.mysql')
        )->execute();
        
        $filename ='comision-'.$comision->materia->codigo;
        return response()->download($output . '.' . $ext, $filename,['Content-Type: application/pdf'])->deleteFileAfterSend();
    }
}

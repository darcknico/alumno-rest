<?php

namespace App\Http\Controllers\Mesa;

use App\User;
use App\Models\Mesa\MesaExamen;
use App\Models\Mesa\MesaExamenMateria;
use App\Models\Mesa\MesaExamenMateriaAlumno;
use App\Models\Mesa\MesaExamenMateriaDocente;
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
use App\Models\Extra\ReporteJob;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use App\Functions\MesaExamenFunction;
use App\Exports\MesaExamenMateriaExport;
use App\Imports\MesaExamenMateriaImport;
use App\Filters\MesaExamenMateriaFilter;
use App\Jobs\RerpoteMesaActa;

use Carbon\Carbon;
use JasperPHP\JasperPHP;

class MesaExamenMateriaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'fecha_ini' => 'date | nullable',
            'fecha_fin' => 'date | nullable',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $id_sede = $request->route('id_sede');
        $id_carrera = $request->route('id_carrera',null);
        $id_materia = $request->route('id_materia',null);
        $registros = MesaExamenMateria::with('carrera','materia.planEstudio','mesa_examen')
            ->whereHas('mesa_examen',function($q)use($id_sede){
                $q->where([
                    'estado' => 1,
                    'sed_id' => $id_sede,
                ]);
            })
            ->where([
            'estado' => 1,
            ]);
        if(!is_null($id_carrera)){
            $todo = $registros
                ->where('id_carrera',$id_carrera)
                ->orderBy('anio','asc')
                ->get();
            return response()->json($todo,200);
        } else if (!is_null($id_materia)){
            $todo = $registros
                ->where('id_materia',$id_materia)
                ->orderBy('anio','asc')
                ->get();
            return response()->json($todo,200);
        }

        $sort = $request->query('sort','fecha');
        $order = $request->query('order','desc');
        $start = $request->query('start',0);
        $length = $request->query('length',0);

        $registros = MesaExamenMateriaFilter::index($request,$registros);

        if(strlen($sort)>0){
            $registros = $registros->orderBy($sort,$order);
        } else {
            $registros = $registros->orderBy('fecha','desc');
        }

        if( $length==0 and $start==0 ){
            $todo = $registros->get();
            return response()->json($todo,200);
        }

        $sql = $registros->toSql();
        $q = clone($registros->getQuery());
        $total_count = $q->groupBy('estado')->count();
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
        $registros = MesaExamenMateria::with('sede','carrera','materia.planEstudio')
            ->where([
                'estado' => 1,
                'car_id' => $id_carrera,
            ])->get();
        return response()->json($todo,200);
    }

    public function materias(Request $request){
        $id_materia = $request->route('id_materia');
        $registros = MesaExamenMateria::with('sede','carrera','materia.planEstudio')
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
        $user = Auth::user();
        $id_sede = $request->route('id_sede');
        $id_materia = $request->route('id_materia');

        $validator = Validator::make($request->all(),[
            'fecha' => 'required | date',
            'fecha_cierre' => 'nullable | date',
            'id_mesa_examen' => 'required',
            'id_materia' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $fecha = $request->input('fecha');
        $fecha_cierre = $request->input('fecha_cierre',null);
        $ubicacion = $request->input('ubicacion',null);
        $id_mesa_examen = $request->input('id_mesa_examen');
        $id_materia = $request->input('id_materia');

        $observaciones = $request->input('observaciones',null);
        $folio_libre = $request->input('folio_libre',null);
        $folio_promocion = $request->input('folio_promocion',null);
        $folio_regular = $request->input('folio_regular',null);
        $libro = $request->input('libro',null);

        $mesa_examen = MesaExamen::find($id_mesa_examen);
        $materia = Materia::find($id_materia);
        if( !$mesa_examen or !$materia){
            return response()->json([
                'error'=>'No se han encontrado la Materia o la Mesa de examen.',
            ],403);
        }

        $todo = MesaExamenMateria::where([
            'estado' => 1,
            'mat_id' => $id_materia,
            'mes_id' => $id_mesa_examen,
        ])->first();
        if($todo){
            return response()->json([
                'error'=>'La mesa de examen ya cuenta con la materia. Elija otra materia disponible.',
            ],403);
        } else {
            $todo = new MesaExamenMateria;
            $todo->id_mesa_examen = $id_mesa_examen;
            $todo->id_carrera = $materia->planEstudio->id_carrera;
            $todo->id_materia = $id_materia;
            $todo->usu_id = $user->id;
            $todo->fecha = $fecha;
            $todo->observaciones = $observaciones;
            $todo->folio_libre = $folio_libre;
            $todo->folio_promocion = $folio_promocion;
            $todo->folio_regular = $folio_regular;
            $todo->libro = $libro;

            if($fecha_cierre){
                $todo->fecha_cierre = Carbon::parse($fecha_cierre);
            }
            $todo->save();
        }
        return response()->json($todo,200);
    }

    /**
    * Inscripcion a una materia de la mesa de examen
    *   Si este no posee una inscripcion previa
    */
    public function alumno_asociar(Request $request){
        $user = Auth::user();
        $id_sede = $request->route('id_sede');
        $id_mesa_examen_materia = $request->route('id_mesa_examen_materia');
        $id_alumno = $request->route('id_alumno');

        $validator = Validator::make($request->all(),[
            'adeuda' => 'boolean | nullable',
            'nota' => 'nullable | integer',
            'id_tipo_condicion_alumno' => 'nullable | integer',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $id_inscripcion = $request->input('id_inscripcion',null);
        $mesa_examen_materia = MesaExamenMateria::find($id_mesa_examen_materia);
        $alumno = Alumno::find($id_alumno);
        if($mesa_examen_materia and $alumno){
            if(empty($id_inscripcion)){
                $inscripcion = Inscripcion::where([
                    'estado' => 1,
                    'sed_id' => $id_sede,
                    'alu_id' => $id_alumno,
                    'car_id' => $mesa_examen_materia->id_carrera,
                ])->orderBy('created_at','desc')->first();
                if($inscripcion){
                    $id_inscripcion = $inscripcion->id;
                } else {
                    return response()->json([
                        'error'=>'No tiene una inscripcion a la carrera de la comision en la sede.',
                    ],403);
                }
            } else {
                $inscripcion = Inscripcion::find($id_inscripcion);
                if(!$inscripcion){
                    return response()->json([
                        'error'=>'La inscripcion no existe.',
                    ],403);
                }
                if($inscripcion->id_alumno != $id_alumno){
                    return response()->json([
                        'error'=>'La inscripcion no pertenece al alumno.',
                    ],403);
                }
            }
            $todo = MesaExamenMateriaAlumno::where([
                'estado' => 1,
                'alu_id' => $id_alumno,
                'mma_id' => $id_mesa_examen_materia,
            ])->first();
            if($todo){
                $todo->usu_id = $user->id;
                $todo->save();
            } else {
                $f = new \NumberFormatter("es", \NumberFormatter::SPELLOUT);
                $id_tipo_condicion_alumno = $request->input('id_tipo_condicion_alumno',1);
                $nota = $request->input('nota');

                $todo = new MesaExamenMateriaAlumno;
                $todo->id_mesa_examen_materia = $id_mesa_examen_materia;
                $todo->id_alumno = $id_alumno;
                $todo->id_inscripcion = $id_inscripcion;
                $todo->nota = $request->input('nota');
                if(!is_null($nota)){
                    $todo->nota_nombre = $f->format($nota);
                }
                $todo->id_tipo_condicion_alumno = $id_tipo_condicion_alumno;
                if($id_tipo_condicion_alumno == 2){
                    $todo->nota_final = $nota;
                    $todo->nota_final_nombre = $f->format($nota);
                }
                $todo->adeuda = $request->input('adeuda',false);
                $todo->usu_id = $user->id;
                $todo->save();
            }
            MesaExamenFunction::actualizar_materia($mesa_examen_materia);
            return response()->json($todo,200);
        }
        return response()->json([
            'error'=>'No se han encontrado la Mesa de examen o el Alumno.',
        ],403);
    }

    public function alumno_desasociar(Request $request){
        $user = Auth::user();
        $id_sede = $request->route('id_sede');
        $id_mesa_examen_materia = $request->route('id_mesa_examen_materia');
        $id_alumno = $request->route('id_alumno');

        $id_inscripcion = $request->input('id_inscripcion',null);
        $mesa_examen_materia = MesaExamenMateria::find($id_mesa_examen_materia);
        $alumno = Alumno::find($id_alumno);
        if($mesa_examen_materia and $alumno){
            $todo = MesaExamenMateriaAlumno::where([
                'estado' => 1,
                'alu_id' => $id_alumno,
                'mma_id' => $id_mesa_examen_materia,
            ])->first();
            if($todo){
                $todo->estado = 0;
                $todo->usu_id_baja = $user->id;
                $todo->deleted_at = Carbon::now();
                $todo->save();
            }
            MesaExamenFunction::actualizar_materia($mesa_examen_materia);
            return response()->json($todo,200);
        }
        return response()->json([
            'error'=>'No se han encontrado la Mesa de examen o el Alumno',
        ],403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id_mesa_examen_materia = $request->route('id_mesa_examen_materia');
        $id_inscripcion = $request->query('id_inscripcion',null);
        $todo = MesaExamenMateria::with([
            'mesa_examen',
            'usuario',
            'carrera',
            'materia.planEstudio',
            'usuario_check_in',
            'usuario_check_out',
        ])->find($id_mesa_examen_materia);
        if(!is_null($id_inscripcion)){
            $id_materia = $todo->id_materia;
            $todo->comision = ComisionAlumno::with('comision')->whereHas('comision',function($q)use($id_materia){
                $q->where('id_materia',$id_materia)->where('estado',1);
            })->where('id_inscripcion',$id_inscripcion)->where('estado',1)->orderBy('created_at','desc')->first();
        }
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
        $id_mesa_examen_materia = $request->route('id_mesa_examen_materia');
        $validator = Validator::make($request->all(),[
            'fecha' => 'required | date',
            'fecha_cierre' => 'nullable | date',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $fecha = $request->input('fecha');
        $fecha_cierre = $request->input('fecha_cierre',null);
        $ubicacion = $request->input('ubicacion',null);
        $observaciones = $request->input('observaciones',null);
        $folio_libre = $request->input('folio_libre',null);
        $folio_promocion = $request->input('folio_promocion',null);
        $folio_regular = $request->input('folio_regular',null);
        $libro = $request->input('libro',null);

        $todo = MesaExamenMateria::find($id_mesa_examen_materia);
        $todo->fecha = Carbon::parse($fecha);
        if(!is_null($fecha_cierre)){
            $todo->fecha_cierre = Carbon::parse($fecha_cierre);
        }
        $todo->ubicacion = $ubicacion;
        $todo->observaciones = $observaciones;
        $todo->folio_libre = $folio_libre;
        $todo->folio_promocion = $folio_promocion;
        $todo->folio_regular = $folio_regular;
        $todo->libro = $libro;
        $todo->save();
        return response()->json($todo,200);
    }

    public function alumnos(Request $request){
        $id_mesa_examen_materia = $request->route('id_mesa_examen_materia');
        $todo = MesaExamenMateriaAlumno::with('alumno','usuario','condicion')
        ->where([
            'estado' => 1,
            'mma_id' => $id_mesa_examen_materia,
        ])->get();
        return response()->json($todo,200);
    }

    public function docentes(Request $request){
        $id_mesa_examen_materia = $request->route('id_mesa_examen_materia');
        $todo = MesaExamenMateriaDocente::with('usuario')
        ->where([
            'estado' => 1,
            'mma_id' => $id_mesa_examen_materia,
        ])->get();
        return response()->json($todo,200);
    }

    public function cerrar(Request $request){
        $user = Auth::user();
        $id_mesa_examen_materia = $request->route('id_mesa_examen_materia');
        $validator = Validator::make($request->all(),[
            'fecha_cierre' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $fecha_cierre = $request->input('fecha_cierre');
        $observaciones = $request->input('observaciones',null);

        $todo = MesaExamenMateria::find($id_mesa_examen_materia);
        $todo->fecha_cierre = Carbon::parse($fecha_cierre);
        $todo->save();
        return response()->json($todo,200);
    }

    public function check_in(Request $request)
    {
        $user = Auth::user();
        $id_mesa_examen_materia = $request->route('id_mesa_examen_materia');
        $mesa_examen = MesaExamenMateria::find($id_mesa_examen_materia);
        if($mesa_examen){
            $mesa_examen->check_in = Carbon::now();
            $mesa_examen->usu_id_check_in = $user->id;
            $mesa_examen->save();
            $fecha = Carbon::parse($mesa_examen->fecha)->toDateString();
            $export = new MesaExamenMateriaExport($id_mesa_examen_materia);
            $export->custom();
            return $export->download('mesa_examen'.$mesa_examen->materia->codigo.'-'.$fecha.'.xlsx');
        }
        return response()->json(['error'=>'La mesa de examen no fue encontrada.'],403);
    }

    public function check_out_previa(Request $request){
        $id_mesa_examen_materia = $request->route('id_mesa_examen_materia');
        $salida = [];
        if($request->hasFile('archivo')){
            $array = (new MesaExamenMateriaImport)->toArray($request->file('archivo'))[0];
            $salida = MesaExamenMateriaController::importar_asistencia_alumno($id_mesa_examen_materia,$array);
        }
        return response()->json($salida,200);
    }

    public function check_out(Request $request){
        $user = Auth::user();
        $id_mesa_examen_materia = $request->route('id_mesa_examen_materia');
        $f = new \NumberFormatter("es", \NumberFormatter::SPELLOUT);
        
        $validator = Validator::make($request->all(),[
            'fecha_cierre' => 'required | date',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $fecha_cierre = $request->input('fecha_cierre',null);
        $observaciones = $request->input('observaciones',null);
        $folio_libre = $request->input('folio_libre',null);
        $folio_promocion = $request->input('folio_promocion',null);
        $folio_regular = $request->input('folio_regular',null);
        $libro = $request->input('libro',null);

        $mesa_examen = MesaExamenMateria::find($id_mesa_examen_materia);
        $salida = [];
        if($request->hasFile('archivo') and $mesa_examen){
            $array = (new MesaExamenMateriaImport)->toArray($request->file('archivo'))[0];
            $asistencias = MesaExamenMateriaController::importar_asistencia_alumno($id_mesa_examen_materia,$array);
            foreach ($asistencias as $asistencia_alumno) {
                $alumno = MesaExamenMateriaAlumno::find($asistencia_alumno['id_mesa_examen_materia_alumno']);
                if($alumno){
                    $alumno->asistencia = $asistencia_alumno['asistencia'];
                    $alumno->nota = $asistencia_alumno['nota'];
                    $alumno->observaciones = $asistencia_alumno['observaciones'];
                    $alumno->save();
                    $salida[]=$alumno;
                }
            }
        } else {
            $asistencias = $request->input('alumnos',[]);
            foreach ($asistencias as $asistencia_alumno) {
                $alumno = MesaExamenMateriaAlumno::find($asistencia_alumno['id']);
                if($alumno){
                    $alumno->asistencia = $asistencia_alumno['asistencia'];
                    $alumno->nota_final = $asistencia_alumno['nota_final'];
                    $alumno->nota_final_nombre = $f->format($asistencia_alumno['nota_final']);
                    $alumno->save();
                    $salida[]=$alumno;
                }
            }
        }
        MesaExamenFunction::actualizar_materia($mesa_examen);
        $mesa_examen->fecha_cierre = $fecha_cierre;
        $mesa_examen->observaciones = $observaciones;
        $mesa_examen->folio_libre = $folio_libre;
        $mesa_examen->folio_promocion = $folio_promocion;
        $mesa_examen->folio_regular = $folio_regular;
        $mesa_examen->libro = $libro;
        $mesa_examen->check_out = Carbon::now();
        $mesa_examen->usu_id_check_out = $user->id;
        $mesa_examen->save();
        return response()->json($salida,200);
    }

    public static function importar_asistencia_alumno($id_mesa_examen_materia,$array){
        $salida = [];
        foreach ($array as $row) {
            if(isset($row['asistencia'])){
                if($row['asistencia']==="Ausente"){
                    $row['asistencia'] = false;
                } else if($row['asistencia']==="Presente") {
                    $row['asistencia'] = true;
                } else {
                    $row['asistencia'] = null;
                }
            } else {
                $row['asistencia'] = null;
            }
            if(isset($row['documento'])){
                $asistencia = MesaExamenMateriaAlumno::where([
                    'estado' => 1,
                    'mma_id' => $id_mesa_examen_materia
                ])->whereHas('alumno',function($q)use($row){
                    $q->where('documento',$row['documento']);
                })->first();
                if($asistencia){
                    $row['id_mesa_examen_materia_alumno'] = $asistencia->id;
                } else {
                    $row['id_mesa_examen_materia_alumno'] = 0;
                }
            } else {
                $row['id_mesa_examen_materia_alumno'] = 0;
            }
            $salida[] = $row;
        }
        return $salida;
    }

    /**
    *   Muestra las mesas que estan disponibles de acuerdo a su carrera
    */
    public function inscripcion_disponibles(Request $request){
        $id_sede = $request->route('id_sede');
        $id_inscripcion = $request->route('id_inscripcion');
        $anio = $request->query('anio',null);

        $inscripcion = Inscripcion::find($id_inscripcion);

        $todo = MesaExamen::where([
            'estado' => 1,
            'sed_id' => $id_sede,
        ])
        ->whereHas('materias',function($q)use($inscripcion){
            $q->where([
                'estado' => 1,
                'car_id' => $inscripcion->id_carrera,
            ]);
        })
        ->when(!is_null($anio),function($q)use($anio){
            $q->whereYear('fecha_inicio','=',$anio);
        })
        ->orderBy('fecha_inicio','desc')
        ->get();
        return response()->json($todo,200);
    }

    /**
    *   Muestra el listado de materias de la mesa de la cual no tenga inscripcion
    */
    public function inscripcion_materias_disponibles(Request $request){
        $id_mesa_examen = $request->route('id_mesa_examen');
        $id_inscripcion = $request->route('id_inscripcion');

        $inscripcion = Inscripcion::find($id_inscripcion);

        $todo = MesaExamenMateria::with('mesa_examen','materia.planEstudio')
        ->whereDoesntHave('alumnos',function($q)use($id_inscripcion){
            $q->where([
                'estado' => 1,
                'ins_id' => $id_inscripcion,
            ]);
        })
        ->where([
            'estado' => 1,
            'mes_id' => $id_mesa_examen,
            'car_id' => $inscripcion->id_carrera,
        ])
        ->orderBy('mat_id','desc')->get();
        $salida = [];
        foreach ($todo as $materia) {
            $materia->inscripcion_ultima = MesaExamenMateriaAlumno::with('mesa_examen_materia.mesa_examen')
            ->where('estado',1)
            ->whereHas('mesa_examen_materia',function($q)use($materia){
                $q->where('id_materia',$materia->id_materia)->where('estado',1);
            })
            ->where('id_inscripcion',$id_inscripcion)
            ->orderBy('created_at','desc')
            ->first();

            $materia->comision = ComisionAlumno::with('comision')->whereHas('comision',function($q)use($materia){
                $q->where('id_materia',$materia->id_materia)->where('estado',1);
            })
            ->where('id_inscripcion',$id_inscripcion)
            ->where('estado',1)
            ->orderBy('created_at','desc')
            ->first();
            $salida[]=$materia;
        }
        return response()->json($salida,200);
    }

    public function inscripcion(Request $request){
        $id_inscripcion = $request->route('id_inscripcion');
        $todo = MesaExamenMateriaAlumno::with(
            'condicion',
            'mesa_examen_materia.mesa_examen',
            'mesa_examen_materia.carrera',
            'mesa_examen_materia.materia.planEstudio',
            'usuario')
            ->where([
                'estado' => 1,
                'ins_id' => $id_inscripcion,
            ])->orderBy('created_at','desc')->get();
        return response()->json($todo,200);
    }

    public function actualizar(Request $request){
        $id_mesa_examen_materia = $request->route('id_mesa_examen_materia');
        $materia = MesaExamenFunction::actualizar_materiaById($id_mesa_examen_materia,true);
        return response()->json($materia);
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
        $id_mesa_examen_materia = $request->route('id_mesa_examen_materia');

        $todo = MesaExamenMateriaAlumno::find($id_mesa_examen_materia);
        if($todo){
            $todo->estado = 0;
            $todo->deleted_at = Carbon::now();
            $todo->usu_id_baja = $user->id;
            $todo->save();
        }
        return response()->json($todo,200);
    }

    public function reporte_acta(Request $request){
        $user = Auth::user();
        $id_mesa_examen_materia = $request->route('id_mesa_examen_materia');

        $validator = Validator::make($request->all(),[
            'id_tipo_condicion_alumno' => 'integer | nullable',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $id_tipo_condicion_alumno = $request->query('id_tipo_condicion_alumno',3);
        $mesa_examen_materia = MesaExamenMateria::find($id_mesa_examen_materia);

        $jasper = new JasperPHP;
        $input = storage_path("app/reportes/alumno_mesa_acta.jasper");
        $output = storage_path("app/reportes/".uniqid());
        $ext = "pdf";

        $jasper->process(
            $input,
            $output,
            [$ext],
            [
                'REPORT_LOCALE' => 'es_AR',
                'id_mesa_examen_materia' => $id_mesa_examen_materia,
                'id_tipo_condicion_alumno' => $id_tipo_condicion_alumno,
                'id_usuario' => $user->id,
                'logo' => storage_path("app/images/logo_2.png"),
            ],
            \Config::get('database.connections.mysql')
        )->execute();
        
        $filename ='acta-'.$id_mesa_examen_materia.'-'.$mesa_examen_materia->id.$ext;
        return response()->download($output . '.' . $ext, $filename)->deleteFileAfterSend();
    }

    public function condiciones(Request $request){
        $todo = TipoCondicionAlumno::where('estado',1)->get();
        return response()->json($todo,200);
    }

    public function reporte_acta_masivo(Request $request){
        $validator = Validator::make($request->all(),[
            'fecha_ini' => 'date | nullable',
            'fecha_fin' => 'date | nullable',
            'nombre' => 'nullable',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $user = Auth::user();
        $id_sede = $request->route('id_sede');
        $registros = MesaExamenMateria::whereHas('mesa_examen',function($q)use($id_sede){
                $q->where([
                    'estado' => 1,
                    'sed_id' => $id_sede,
                ]);
            })
            ->where([
                'estado' => 1,
            ]);

        $registros = MesaExamenMateriaFilter::index($request,$registros);
        $todo = $registros->get()->pluck('id')->toArray();

        if($todo==0){
            return response()->json([
                'error' => 'No puede realizar la peticion si la cantidad de reportes a generar es cero.'
            ],403);
        }
        $fecha = Carbon::now();
        $cantidad = count($todo);

        $nombre = $request->input('nombre',null);

        $reporte = new ReporteJob;
        if(is_null($nombre)){
            $reporte->nombre = $fecha->format('d-m-Y').'_mesa_examen_materia_'.$cantidad;
        } else {
            $reporte->nombre = $nombre;
        }
        $reporte->cantidad = $cantidad;
        $reporte->ruta = $request->getRequestUri();
        $reporte->id_usuario = $user->id;
        $reporte->id_sede = $id_sede;
        $reporte->save();

        RerpoteMesaActa::dispatch($todo,$reporte);

        return response()->json($reporte,200);
    }

}
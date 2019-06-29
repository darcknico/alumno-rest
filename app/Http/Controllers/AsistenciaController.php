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
use App\Models\TipoAsistenciaAlumno;
use App\Models\Asistencia;
use App\Models\AsistenciaAlumno;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use Carbon\Carbon;

use App\Exports\AsistenciaExport;
use App\Imports\AsistenciaImport;

class AsistenciaController extends Controller
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
        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);
        
        $registros = Asistencia::with('comision.carrera','comision.materia','usuario_check_in','usuario_check_out')
            ->whereHas('comision',function($q)use($id_sede){
                $q->where([
                    'estado' => 1,
                    'sed_id' => $id_sede,
                ]);
            })
            ->where([
            'estado' => 1,
        ]);

        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros->orderBy('created_at','desc')
            ->get();
            return response()->json($todo,200);
        }

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
            ->when(!empty($anio),function($q)use($anio){
                return $q->whereHas('comision',function($q)use($anio){
                    $q->where('com_anio',$anio);
                });
            })
            ->when($user->id_tipo_usuario == 8,function($q)use($user){
                return $q->whereHas('comision',function($q)use($user){
                    $q->where('id_usuario',$user->id);
                });
            })
            ->when($id_comision>0,function($q)use($id_comision){
                return $q->where('id_comision',$id_comision);
            });
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
                if(strlen($value)>0){
                    $registros = $registros->where(function($query) use  ($value) {
                        $query->whereRaw("DATE_FORMAT(asi_fecha, '%d/%m/%Y') ",'like','%'.$value.'%');
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
          return response()->json(['error'=>$validator->errors()],404);
        }
        $fecha = $request->input('fecha');

        $comision = Comision::find($id_comision);
        if(!$comision){
            return response()->json(['error'=>'La comision no fue encontrada.'],404);
        }

        $todo = new Asistencia;
        $todo->fecha = $fecha;
        $todo->alumnos_cantidad = $comision->alumnos_cantidad;
        $todo->id_comision = $id_comision;
        $todo->usu_id = $user->id;
        $todo->save();

        $asistentes = ComisionAlumno::where([
            'com_id' => $id_comision,
            'estado' => 1,
        ])->pluck('alu_id')->toArray();

        foreach ($asistentes as $asistente) {
            $alumno = new AsistenciaAlumno;
            $alumno->id_asistencia = $todo->id;
            $alumno->id_alumno = $asistente;
            $alumno->save();
        }

        return response()->json($todo,200);
    }

    public function alumno(Request $request){
        $id_asistencia = $request->route('id_asistencia');
        $id_alumno = $request->route('id_alumno');
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'id_tipo_asistencia_alumno' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],404);
        }
        $id_tipo_asistencia_alumno = $request->input('id_tipo_asistencia_alumno');
        $observaciones = $request->input('observaciones');

        $alumno = AsistenciaAlumno::where([
            'estado' => 1,
            'asi_id' => $id_asistencia,
            'alu_id' => $id_alumno,
        ])->first();
        if($alumno){
            $alumno->id_tipo_asistencia_alumno = $id_tipo_asistencia_alumno;
            $alumno->observaciones = $observaciones;
            $alumno->save();

            $asistencia = Asistencia::find($id_asistencia);
            $alumnos_cantidad_presente = AsistenciaAlumno::selectRaw('count(*) as total')
                ->where([
                    'estado' => 1,
                    'asi_id' => $id_asistencia,
                    'taa_id' => 4,
                ])->groupBy('asi_id')->first();
            $asistencia->alumnos_cantidad_presente = $alumnos_cantidad_presente->total??0;
            $asistencia->save();
            return response()->json($asistencia,200);
        }

        return response()->json(['error'=>'La asistencia no fue encontrada.'],404);
    }

    public function alumnos(Request $request){
        $id_asistencia = $request->route('id_asistencia');
        $todo = AsistenciaAlumno::with('alumno.tipoDocumento','tipo')->where([
            'estado' => 1,
            'asi_id' => $id_asistencia,
        ])->get()->sortBy(function ($batch) { 
            return $batch->alumno->apellido; 
        })->values();
        return response()->json($todo,200);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id_asistencia = $request->route('id_asistencia');
        $todo = Asistencia::with([
            'usuario_check_in',
            'usuario_check_out',
        ])->find($id_asistencia);
        return response()->json($todo,200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function check_in(Request $request)
    {
        $user = Auth::user();
        $id_asistencia = $request->route('id_asistencia');
        $asistencia = Asistencia::find($id_asistencia);
        if($asistencia){
            $asistencia->check_in = Carbon::now();
            $asistencia->usu_id_check_in = $user->id;
            $asistencia->save();
            $fecha = Carbon::parse($asistencia->fecha)->toDateString();
            $export = new AsistenciaExport($id_asistencia);
            $export->custom();
            return $export->download('asistencia'.$asistencia->comision->materia->codigo.'-'.$fecha.'.xlsx');
        }
        return response()->json(['error'=>'La asistencia no fue encontrada.'],404);
    }

    public function check_out_previa(Request $request){
        $id_asistencia = $request->route('id_asistencia');
        $salida = [];
        if($request->hasFile('archivo')){
            $array = (new AsistenciaImport)->toArray($request->file('archivo'))[0];
            $salida = AsistenciaController::importar_asistencia_alumno($id_asistencia,$array);
        }
        return response()->json($salida,200);
    }

    public function check_out(Request $request){
        $user = Auth::user();
        $id_asistencia = $request->route('id_asistencia');
        $asistencia = Asistencia::find($id_asistencia);
        $salida = [];
        if($request->hasFile('archivo') and $asistencia){
            $array = (new AsistenciaImport)->toArray($request->file('archivo'))[0];
            $asistencias = AsistenciaController::importar_asistencia_alumno($id_asistencia,$array);
            foreach ($asistencias as $asistencia_alumno) {
                $alumno = AsistenciaAlumno::find($asistencia_alumno['id_asistencia_alumno']);
                if($alumno){
                    $alumno->id_tipo_asistencia_alumno = $asistencia_alumno['id_tipo_asistencia_alumno'];
                    $alumno->observaciones = $asistencia_alumno['observaciones'];
                    $alumno->save();
                    $salida[]=$alumno;
                }
            }
            $alumnos_cantidad_presente = AsistenciaAlumno::selectRaw('count(*) as total')
                ->where([
                    'estado' => 1,
                    'asi_id' => $id_asistencia,
                    'taa_id' => 4,
                ])->groupBy('asi_id')->first();
            $asistencia->alumnos_cantidad_presente = $alumnos_cantidad_presente->total??0;
            $asistencia->check_out = Carbon::now();
            $asistencia->usu_id_check_out = $user->id;
            $asistencia->responsable_nombre = $user->nombre;
            $asistencia->responsable_apellido = $user->apellido;
            $asistencia->save();
        }
        return response()->json($salida,200);
    }

    public static function importar_asistencia_alumno($id_asistencia,$array){
        $salida = [];
        foreach ($array as $row) {
            if(isset($row['asistencia'])){
                $tipo_asistencia = TipoAsistenciaAlumno::where('nombre','like',$row['asistencia'])->first();
                if($tipo_asistencia){
                    $row['id_tipo_asistencia_alumno'] = $tipo_asistencia->id;
                } else {
                    $row['id_tipo_asistencia_alumno'] = 0;
                }
            } else {
                $row['id_tipo_asistencia_alumno'] = 0;
            }
            if(isset($row['asistencia'])){
                $asistencia = AsistenciaAlumno::where([
                    'estado' => 1,
                    'asi_id' => $id_asistencia
                ])->whereHas('alumno',function($q)use($row){
                    $q->where('documento',$row['documento']);
                })->first();
                if($asistencia){
                    $row['id_asistencia_alumno'] = $asistencia->id;
                } else {
                    $row['id_asistencia_alumno'] = 0;
                }
            } else {
                $row['id_asistencia_alumno'] = 0;
            }
            $salida[] = $row;
        }
        return $salida;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $id_asistencia = $request->route('id_asistencia');

        $todo = Asistencia::find($id_asistencia);
        if($todo){
            $todo->estado = 0;
            $todo->deleted_at = Carbon::now();
            $todo->usu_id_baja = $user->id;
            $todo->save();
        }
        return response()->json($todo,200);
    }

    public function tipos(Request $request){
        $todo = TipoAsistenciaAlumno::where('estado',1)->get();
        return response()->json($todo,200);
    }

}

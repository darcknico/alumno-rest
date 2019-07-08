<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Sede;
use App\Models\Comision;
use App\Models\ComisionAlumno;
use App\Models\Alumno;
use App\Models\AlumnoArchivo;
use App\Models\AlumnoNotificacion;
use App\Models\TipoAlumnoCivil;
use App\Models\TipoAlumnoEstado;
use App\Models\TipoAlumnoDocumentacion;
use App\Models\TipoInscripcionEstado;
use App\Models\Departamento;
use App\Models\Carrera;
use App\Models\PlanEstudio;
use App\Models\Materia;
use App\Models\Inscripcion;
use App\Models\Beca;
use App\Models\PlanPago;
use App\Models\Pago;
use App\Models\Obligacion;
use App\Models\ObligacionPago;
use App\Models\ObligacionInteres;
use App\Models\Modalidad;
use App\Models\Movimiento;
use App\Models\Extra\Provincia;

use App\Exports\AlumnoExport;
use App\Imports\AlumnoImport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use Carbon\Carbon;
use App\Functions\CorreoFunction;
use App\Functions\CuentaCorrienteFunction;
use App\Functions\DiariaFunction;
use Maatwebsite\Excel\Facades\Excel;

class AlumnoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $id_sede = $request->route('id_sede',null);
        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);
        $registros = Alumno::with([
            'tipoDocumento',
            'tipo_civil',
            'tipo_estado',
            'provincia',
            'inscripciones'=>function($q){
                $q->where('estado',1);
            },
            'archivos'=>function($q){
                $q->where('estado',1);
            },
        ])
        ->when(!empty($id_sede),function($q)use($id_sede){
            return $q->where('sed_id',$id_sede);
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
        $id_tipo_alumno_estado = $request->query('id_tipo_alumno_estado',0);

        $registros = $registros
            ->when($id_departamento>0,function($q)use($id_departamento){
                $carreras = Carrera::where([
                    'dep_id' => $id_departamento,
                    'estado' => 1,
                ])->pluck('car_id')->toArray();
                $inscripciones = Inscripcion::where([
                    'estado' => 1,
                ])
                ->whereIn('car_id',$carreras)
                ->pluck('alu_id')->toArray();
                return $q->whereIn('alu_id',$inscripciones);
            })
            ->when($id_carrera>0,function($q)use($id_carrera){
                $inscripciones = Inscripcion::where([
                    'car_id' => $id_carrera,
                    'estado' => 1,
                ])->pluck('alu_id')->toArray();
                return $q->whereIn('alu_id',$inscripciones);
            })
            ->when($id_tipo_alumno_estado>0,function($q)use($id_tipo_alumno_estado){
                return $q->where('tae_id',$id_tipo_alumno_estado);
            });
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
              if(strlen($value)>0){
                $registros = $registros->where(function($query) use  ($value) {
                  $query->where('alu_nombre','like','%'.$value.'%')
                    ->orWhere('alu_apellido','like','%'.$value.'%')
                    ->orWhere('alu_localidad','like','%'.$value.'%')
                    ->orWhere('alu_calle','like','%'.$value.'%')
                    ->orWhere('alu_domicilio','like','%'.$value.'%')
                    ->orWhere('alu_documento','like',$value.'%');
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

    public function exportar(Request $request){
        $id_sede = $request->route('id_sede');
        $search = $request->query('search','');
        $id_departamento = $request->query('id_departamento',0);
        $id_carrera = $request->query('id_carrera',0);
        $id_tipo_alumno_estado = $request->query('id_tipo_alumno_estado',0);

        $fecha = Carbon::now()->format('d.m.Y');

        return (new AlumnoExport($id_sede,$search,$id_departamento,$id_carrera,$id_tipo_alumno_estado))->download('alumno'.$fecha.'.xlsx');
    }

    public function estadisticas(Request $request){
        $totales = \DB::table('tbl_alumnos')
        ->selectRaw('
            sum(if(tae_id=1,1,0)) as no_inscriptos,
            sum(if(tae_id=2,1,0)) as inscriptos,
            sum(if(tae_id=3,1,0)) as baja
            ')
        ->where([
            'estado' => 1,
        ])
        ->groupBy('sed_id')
        ->first();
        if(!$totales){
            $totales['no_inscriptos'] = 0;
            $totales['inscriptos'] = 0;
            $totales['baja'] = 0;
        }

        $totales_hoy = \DB::table('tbl_alumnos')
        ->selectRaw('
            sum(if(tae_id=1,1,0)) as no_inscriptos,
            sum(if(tae_id=2,1,0)) as inscriptos,
            sum(if(tae_id=3,1,0)) as baja
            ')
        ->where([
            'estado' => 1,
        ])
        ->whereYear('created_at',Carbon::now()->year)
        ->groupBy('sed_id')
        ->first();
        if(!$totales_hoy){
            $totales_hoy['no_inscriptos'] = 0;
            $totales_hoy['inscriptos'] = 0;
            $totales_hoy['baja'] = 0;
        }

        return response()->json([
            'totales' => $totales,
            'totales_hoy' => $totales_hoy,
        ], 200);
    }

    public function buscar(Request $request){
      $documento = $request->query('documento','');
      $todo = Alumno::with([
            'tipoDocumento',
            'tipo_civil',
            'tipo_estado',
            'provincia',
            'inscripciones'=>function($q){
                $q->where('estado',1);
            },
            'archivos'=>function($q){
                $q->where('estado',1);
            },
        ])
        ->where([
            'estado' => 1,
        ])
        ->where('alu_documento','like',$documento.'%')
        ->orderBy('alu_apellido','desc')
        ->limit(5)
        ->get();
      return response()->json($todo, 200);
    }

    public function concidencias(Request $request){
        $documento = $request->query('documento','');
        $id_tipo_documento = $request->query('id_tipo_documento',96);
        $id_sede = $request->route('id_sede');
        if( empty($documento) and empty($id_tipo_documento)){
            return response()->json(['coincidencia'=>true], 200);
        }
        $alumno = Alumno::where('id_tipo_documento',$id_tipo_documento)
            ->where('id_sede',$id_sede)
            ->where('documento',$documento)
            ->where('estado',1)
            ->first();
        $coincidencia = false;
        if($alumno){
            $coincidencia = true;
        }
        return response()->json([
            'coincidencia' => true,
            'alumno' => $alumno,
        ], 200);
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
            'nombre' => 'required',
            'documento' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $nombre = $request->input('nombre');
        $apellido = $request->input('apellido');
        $fecha_alta = $request->input('fecha_alta');
        $codigo = $request->input('codigo');
        $domicilio = $request->input('domicilio');
        $calle = $request->input('calle');
        $numero = $request->input('numero');
        $piso = $request->input('piso');
        $depto = $request->input('depto');
        $id_localidad = $request->input('id_localidad',0);
        $localidad = $request->input('localidad');
        $id_provincia = $request->input('id_provincia');
        $codigo_postal = $request->input('codigo_postal');
        $telefono = $request->input('telefono');
        $celular = $request->input('celular');
        $email = $request->input('email');
        $id_tipo_documento = $request->input('id_tipo_documento');
        $documento = $request->input('documento');
        $fecha_nacimiento = $request->input('fecha_nacimiento');
        $ciudad_nacimiento = $request->input('ciudad_nacimiento');
        $nacionalidad = $request->input('nacionalidad');
        $sexo = $request->input('sexo');
        $id_tipo_alumno_civil = $request->input('id_tipo_alumno_civil');
        $id_tipo_alumno_estado = $request->input('id_tipo_alumno_estado',1);
        $observaciones = $request->input('observaciones');

        $todo = new Alumno;
        $todo->sed_id = $id_sede;
        $todo->alu_nombre = $nombre;
        $todo->alu_apellido = $apellido;
        $todo->alu_fecha_alta = $fecha_alta;
        $todo->alu_codigo = $codigo;
        $todo->alu_domicilio = $domicilio;
        $todo->alu_calle = $calle;
        $todo->alu_numero = $numero;
        $todo->alu_piso = $piso;
        $todo->alu_depto = $depto;
        $todo->loc_id = $id_localidad;
        $todo->alu_localidad = $localidad;
        $todo->pro_id = $id_provincia;
        $todo->alu_codigo_postal = $codigo_postal;
        $todo->alu_telefono = $telefono;
        $todo->alu_celular = $celular;
        $todo->alu_email = $email;
        $todo->tdo_id = $id_tipo_documento;
        $todo->alu_documento = $documento;
        $todo->alu_fecha_nacimiento = Carbon::parse($fecha_nacimiento);
        $todo->ciudad_nacimiento = $ciudad_nacimiento;
        $todo->alu_nacionalidad = $nacionalidad;
        $todo->alu_sexo = $sexo;
        $todo->tac_id = $id_tipo_alumno_civil;
        $todo->tae_id = $id_tipo_alumno_estado;
        $todo->alu_observaciones = $observaciones;
        $todo->usu_id = $user->id;
        $todo->save();
        return response()->json($todo,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id_alumno = $request->alumno;
        $todo = Alumno::with([
            'tipoDocumento',
            'tipo_civil',
            'tipo_estado',
            'provincia',
            'archivos'=>function($q){
                $q->where('estado',1);
            },
            'archivos.tipo_documentacion',
            'notificaciones.usuario',
        ])->find($id_alumno);
        return response()->json($todo,200);
    }

    public function estado_deuda(Request $request){
        $id_alumno = $request->route('id_alumno');
        $inscripciones = Inscripcion::where([
            'estado' => 1,
            'alu_id' => $id_alumno,
        ])->pluck('id')->toArray();

        $planes_pago = PlanPago::where('estado',1)->whereIn('id_inscripcion',$inscripciones)->orderBy('anio','asc')->get();
        $deuda = 0;
        $primera = null;
        foreach ($planes_pago as $plan_pago) {
            if($plan_pago->saldo_hoy>0){
                $deuda = $deuda + $plan_pago->saldo_hoy;
                if(is_null($primera)){
                    $primera = $plan_pago->id;
                }
            }
        }

        return response()->json([
            'deuda' => $deuda,
            'id_plan_pago' => $primera,
        ],200);
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
        $id_sede = $request->route('id_sede');
        $id_alumno = $request->alumno;
        $validator = Validator::make($request->all(),[
            'nombre' => 'required',
            'documento' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $nombre = $request->input('nombre');
        $apellido = $request->input('apellido');
        $fecha_alta = $request->input('fecha_alta');
        $codigo = $request->input('codigo');
        $domicilio = $request->input('domicilio');
        $calle = $request->input('calle');
        $numero = $request->input('numero');
        $piso = $request->input('piso');
        $depto = $request->input('depto');
        $id_localidad = $request->input('id_localidad',0);
        $localidad = $request->input('localidad');
        $id_provincia = $request->input('id_provincia');
        $codigo_postal = $request->input('codigo_postal');
        $telefono = $request->input('telefono');
        $celular = $request->input('celular');
        $email = $request->input('email');
        $id_tipo_documento = $request->input('id_tipo_documento');
        $documento = $request->input('documento');
        $fecha_nacimiento = $request->input('fecha_nacimiento');
        $ciudad_nacimiento = $request->input('ciudad_nacimiento');
        $nacionalidad = $request->input('nacionalidad');
        $sexo = $request->input('sexo');
        $id_tipo_alumno_civil = $request->input('id_tipo_alumno_civil');
        $observaciones = $request->input('observaciones');

        $todo = Alumno::find($id_alumno);
        $todo->alu_nombre = $nombre;
        $todo->alu_apellido = $apellido;
        $todo->alu_fecha_alta = $fecha_alta;
        $todo->alu_codigo = $codigo;
        $todo->alu_domicilio = $domicilio;
        $todo->alu_calle = $calle;
        $todo->alu_numero = $numero;
        $todo->alu_piso = $piso;
        $todo->alu_depto = $depto;
        $todo->loc_id = $id_localidad;
        $todo->alu_localidad = $localidad;
        $todo->pro_id = $id_provincia;
        $todo->alu_codigo_postal = $codigo_postal;
        $todo->alu_telefono = $telefono;
        $todo->alu_celular = $celular;
        $todo->alu_email = $email;
        $todo->tdo_id = $id_tipo_documento;
        $todo->alu_documento = $documento;
        $todo->alu_fecha_nacimiento = Carbon::parse($fecha_nacimiento);
        $todo->ciudad_nacimiento = $ciudad_nacimiento;
        $todo->alu_nacionalidad = $nacionalidad;
        $todo->alu_sexo = $sexo;
        $todo->tac_id = $id_tipo_alumno_civil;
        $todo->alu_observaciones = $observaciones;
        $todo->save();
        return response()->json($todo,200);
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
        $id_alumno = $request->alumno;
        $alumno = Alumno::find($id_alumno);
        $alumno->usu_id_baja = $user->id;
        $alumno->deleted_at = Carbon::now();
        $alumno->estado = 0;
        $alumno->save();

        $inscripciones = Inscripcion::where([
            'alu_id' => $id_alumno,
            'estado' => 1,
        ])
        ->get();
        foreach ($inscripciones as $inscripcion) {
            $inscripcion = Inscripcion::find($inscripcion->id);
            $inscripcion->estado = 0;
            $inscripcion->save();

            $planes_pago = PlanPago::where([
                'ins_id' => $inscripcion->id,
                'estado' => 1,
            ])->get();

            foreach ($planes_pago as $plan_pago) {
                $plan_pago = PlanPago::find($plan_pago->id);
                $pagos = Pago::where('id_plan_pago',$plan_pago)->where('estado',1)->get();
                foreach ($pagos as $pago) {
                  $pago = Pago::find($pago->id);
                  $pago->estado = 0;
                  $pago->save();
                  $movimiento = Movimiento::find($pago->id_movimiento);
                  $movimiento->estado = 0;
                  $movimiento->usu_id_baja = $user->id;
                  $movimiento->deleted_at = Carbon::now();
                  $movimiento->save();
                  DiariaFunction::quitar($id_sede,$id_movimiento);
                }
                $obligaciones = Obligacion::where('id_plan_pago',$plan_pago->id)->where('estado',1)->get();
                foreach ($obligaciones as $obligacion) {
                  $obligacion = Obligacion::find($obligacion->id);
                  $obligacion->estado = 0;
                  $obligacion->save();
                  ObligacionPago::where([
                    'obl_id' => $obligacion->id,
                    'estado' => 1,
                  ])->update([
                    'estado' => 0
                  ]);
                  ObligacionInteres::where([
                    'obl_id' => $obligacion->id,
                    'estado' => 1,
                  ])->update([
                    'estado' => 0,
                  ]);
                }

                $plan_pago->estado = 0;
                $plan_pago->usu_id_baja = $user->id;
                $plan_pago->deleted_at = Carbon::now();
                $plan_pago->save();
            }
        }

        $comisiones = ComisionAlumno::where([
            'alu_id' => $id_alumno,
            'estado' => 1,
        ])
        ->get();
        foreach ($comisiones as $comision) {
            $comision_alumno = ComisionAlumno::find($comision->id);
            $comision_alumno->usu_id_baja = $user->id;
            $comision_alumno->deleted_at = Carbon::now();
            $comision_alumno->estado = 0;
            $comision_alumno->save();

            $comision = Comision::find($comision_alumno->id_comision);
            $alumnos_cantidad = ComisionAlumno::selectRaw('count(*) as total')
                ->where([
                    'estado' => 1,
                    'com_id' => $comision_alumno->id_comision,
                ])->groupBy('com_id')->first();
            $comision->alumnos_cantidad = $alumnos_cantidad->total??0;
            $comision->save();
        }
        return response()->json($alumno,200);
    }

    public function archivoAlta(Request $request){
        $user = Auth::user();
        $id_alumno = $request->route('id_alumno');
        $validator = Validator::make($request->all(),[
            'archivo' => 'required',
            'id_tipo_alumno_documentacion' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $todo = null;
        if($request->hasFile('archivo')){
            $id_tipo_alumno_documentacion = $request->input('id_tipo_alumno_documentacion');
            $archivo = $request->file('archivo');
            $filename = $archivo->store('alumnos/archivos');
            $todo = new AlumnoArchivo;
            $todo->alu_id = $id_alumno;
            $todo->aar_nombre = $archivo->getClientOriginalName();
            $todo->aar_dir = $filename;
            $todo->tad_id = $id_tipo_alumno_documentacion;
            $todo->usu_id = $user->id;
            $todo->save();

            $todo = AlumnoArchivo::with('tipo_documentacion')->find($todo->aar_id);
        }
        return response()->json($todo,200);
    }

    public function archivoBaja(Request $request){
        $id_alumno_archivo = $request->route('id_alumno_archivo');
        $todo = AlumnoArchivo::find($id_alumno_archivo);
        if($todo){
            Storage::delete($todo->aar_dir);
            $todo->estado = 0;
            $todo->save();
        }
        return response()->json($todo,200);
    }

    public function archivo(Request $request){
        $id_alumno_archivo = $request->route('id_alumno_archivo');
        $todo = AlumnoArchivo::find($id_alumno_archivo);
        return response()->download(storage_path("app/{$todo->aar_dir}"));
    }

    public function inscripcion_store(Request $request){
        $user = Auth::user();
        $id_sede = $request->route('id_sede');
        $id_alumno = $request->route('id_alumno');
        $validator = Validator::make($request->all(),[
            'id_carrera' => 'required',
            'id_plan_estudio' => 'required',
            'anio' => 'required',
            'matricula_monto' => 'required',
            'cuota_monto' => 'required',
            'interes_monto' => 'required',
            'id_beca' => 'required',
            'id_modalidad' => 'required',
            'beca_nombre' => 'required',
            'beca_porcentaje' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $id_carrera = $request->input('id_carrera');
        $id_plan_estudio = $request->input('id_plan_estudio');
        $anio = $request->input('anio');
        $matricula_monto = $request->input('matricula_monto');
        $cuota_monto = $request->input('cuota_monto');
        $interes_monto = $request->input('interes_monto');
        $id_beca = $request->input('id_beca');
        $id_modalidad = $request->input('id_modalidad');
        $beca_nombre = $request->input('beca_nombre');
        $beca_porcentaje = $request->input('beca_porcentaje');

        $alumno = Alumno::find($id_alumno);
        if($alumno->id_tipo_alumno_estado == 1){
            $alumno->id_tipo_alumno_estado = 2;
            $alumno->save();    
        }

        $beca = Beca::find($id_beca);
        
        $todo = new Inscripcion;
        $todo->id_alumno = $id_alumno;
        $todo->id_carrera = $id_carrera;
        $todo->id_plan_estudio = $id_plan_estudio;
        $todo->id_sede = $id_sede;
        $todo->anio = $anio;
        $todo->id_usuario = $user->id;
        $todo->id_modalidad = $id_modalidad;
        if($beca){
            $todo->id_beca = $id_beca;
            $todo->beca_nombre = $beca_nombre;
            $todo->beca_porcentaje = $beca_porcentaje;
        }
        $todo->save();

        $plan_pago = new PlanPago;
        $plan_pago->id_sede = $id_sede;
        $plan_pago->id_inscripcion = $todo->id;
        $plan_pago->matricula_monto = $matricula_monto;
        $plan_pago->matricula_saldo = $matricula_monto;
        $plan_pago->cuota_monto = $cuota_monto;
        $plan_pago->interes_monto = $interes_monto;
        $plan_pago->anio = $anio;
        $plan_pago->id_usuario = $user->id;
        $plan_pago->save();
        $detalle = AlumnoController::preparar_obligaciones($anio,$matricula_monto,$cuota_monto,$beca_porcentaje);
        $primero = true;
        foreach ($detalle['obligaciones'] as $obligacion) {
            if($primero){
                $primero = false;
                $matricula = new Obligacion;
                $matricula->id_plan_pago = $plan_pago->id;
                $matricula->id_tipo_obligacion = 10;
                $matricula->descripcion = $obligacion['descripcion'];
                $matricula->monto = $obligacion['monto'];
                $matricula->saldo = $obligacion['saldo'];
                $matricula->fecha = $obligacion['fecha'];
                $matricula->id_usuario = $user->id;
                $matricula->fecha_vencimiento = $obligacion['fecha_vencimiento'];
                $matricula->save();
            } else {
                $cuota = new Obligacion;
                $cuota->id_plan_pago = $plan_pago->id;
                $cuota->id_tipo_obligacion = 1;
                $cuota->descripcion = $obligacion['descripcion'];
                $cuota->monto = $obligacion['monto'];
                $cuota->saldo = $obligacion['saldo'];
                $cuota->fecha = $obligacion['fecha'];
                $cuota->fecha_vencimiento = $obligacion['fecha_vencimiento'];
                $cuota->id_usuario = $user->id;
                $cuota->save();
            }
        }
        CuentaCorrienteFunction::armar($id_sede,$plan_pago->id);

        if(!empty($alumno->email)){
            $sede = Sede::find($id_sede);
            $alumno = Alumno::with('tipoDocumento','provincia')->find($id_alumno);
            $carrera = Carrera::find($id_carrera);
            $plan_estudio = PlanEstudio::find($id_plan_estudio);
            $materias = Materia::where([
                'pes_id' => $id_plan_estudio,
                'estado' => 1,
            ])->orderBy('tml_id','asc')->get();
            $token = bin2hex(random_bytes(64));
            $logo = CorreoFunction::logo();
            $logo = $logo."?token=".$token;
                Mail::send('mails.inscripcion',[
                    'logo' => $logo,
                    'alumno' => $alumno,
                    'carrera' => $carrera,
                    'plan_estudio' => $plan_estudio,
                    'materias' => $materias,
                    'sede' => $sede,
                ], function($message) use ($user,$carrera,$alumno){
                    $message->from($user->email, $user->apellido." ".$user->nombre);
                    $message->replyTo("no-replay@prueba.com","No Responder");
                    $message->to($alumno->email)->subject("Inscripcion a Carrera ".$carrera->nombre);
                    
                });
                
                if (Mail::failures()) {
                    $enviado = false;
                } else {
                    $enviado = true;
                }
            $notificacion = new AlumnoNotificacion;
            $notificacion->alu_id = $id_alumno;
            $notificacion->usu_id = $user->id;
            $notificacion->ano_enviado = $enviado;
            $notificacion->ano_token = $token;
            $notificacion->ano_email = $alumno->email;
            $notificacion->save();
        }

        return response()->json($todo,200);
        
    }

    public function inscripcion_preparar(Request $request){
        $user = Auth::user();
        $id_sede = $request->route('id_sede');
        $id_alumno = $request->route('id_alumno');
        $validator = Validator::make($request->all(),[
            'anio' => 'required',
            'matricula_monto' => 'required',
            'cuota_monto' => 'required',
            'interes_monto' => 'required',
            'id_beca' => 'required',
            'beca_nombre' => 'required',
            'beca_porcentaje' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $anio = $request->input('anio');
        $matricula_monto = $request->input('matricula_monto');
        $cuota_monto = $request->input('cuota_monto');
        $interes_monto = $request->input('interes_monto');
        $id_beca = $request->input('id_beca');
        $beca_nombre = $request->input('beca_nombre');
        $beca_porcentaje = $request->input('beca_porcentaje');

        $detalle = AlumnoController::preparar_obligaciones($anio,$matricula_monto,$cuota_monto,$beca_porcentaje);

        return response()->json($detalle['obligaciones'],200);
    }
    public static function preparar_obligaciones($anio,$matricula_monto,$cuota_monto,$beca_porcentaje){
        $fecha = Carbon::createFromDate($anio,2, 1);
        $obligaciones = [];
        $matricula = [
            'descripcion' => 'Matricula Inscripcion',
            'monto' => $matricula_monto,
            'saldo' => $matricula_monto,
            'fecha' => $fecha->toDateString(),
            'fecha_vencimiento' => $fecha->addDays(9)->toDateString(),
        ];

        $obligaciones[] = $matricula;
        $total = $matricula_monto;
        if($beca_porcentaje>0){
            $cuota_monto = $cuota_monto - $cuota_monto*($beca_porcentaje/100);
        }
        for ($i=3; $i <=12 ; $i++) {
            $fecha = Carbon::createFromDate($anio,$i, 1);
            $obligacion = [
                'descripcion' => 'Cuota '.$fecha->formatLocalized('%B').' '.$anio,
                'monto' => $cuota_monto,
                'saldo' => $cuota_monto,
                'fecha' => $fecha->toDateString(),
                'fecha_vencimiento' => $fecha->addDays(9)->toDateString(),
            ];
            $total += $cuota_monto;
            $obligaciones[] = $obligacion;
        }

        return [
            'total' => $total,
            'obligaciones' => $obligaciones,
        ];
    }

    public function inscripciones(Request $request){
        $id_sede  = $request->route('id_sede',0);
        $id_alumno  = $request->route('id_alumno');
        $todo = Inscripcion::with('sede','usuario','beca','carrera.departamento','plan_estudio','tipo_estado','modalidad')
        ->where([
            'estado' => 1,
            'alu_id' => $id_alumno,
        ])
        ->when($id_sede>0,function($q)use($id_sede){
            $q->where('id_sede',$id_sede);
        })
        ->get();
        return response()->json($todo,200);
    }

    public function tipos_estado(Request $request){
        $todo = TipoAlumnoEstado::where('estado',1)->get();
        return response()->json($todo,200);
    }

    public function tipos_civil(Request $request){
        $todo = TipoAlumnoCivil::where('estado',1)->get();
        return response()->json($todo,200);
    }

    public function tipos_documentacion(Request $request){
        $todo = TipoAlumnoDocumentacion::where('estado',1)->get();
        return response()->json($todo,200);
    }

    public function importar_previa(Request $request){
        $id_sede = $request->route('id_sede');
        $salida = [];
        $inicio = Carbon::now();
        if($request->hasFile('archivo')){
            $array = (new AlumnoImport)->toCollection($request->file('archivo'))[0];
            $salida = AlumnoController::importar_alumno($id_sede,$array);
        }
        $cantidad = count($salida);
        $fin = Carbon::now();
        $consolidad = [];
        $no_consolidado = [];
        foreach ($salida as $row) {
            $id_alumno = $row['id_alumno'];
            $id_carrera = $row['id_carrera'];
            $id_tecnicatura = $row['id_tecnicatura']??0;
            $id_plan_estudio = $row['id_plan_estudio'];
            $id_inscripcion = $row['id_inscripcion'];
            $id_modalidad = $row['id_modalidad'];
            $id_beca = $row['id_beca'];
            $cuota_monto = $row['cuota'];
            $matricula_monto = $row['matricula'];
            $anio = $row['anio'];
            if (is_null($id_alumno) or is_null($id_carrera) or is_null($id_inscripcion) or is_null($id_beca) or is_null($id_modalidad) or is_null($id_plan_estudio)) {
                $no_consolidado[]= $row;
            } else {
                $consolidad[]= $row;
            }
        }
        return response()->json([
            'inicio'=>$inicio,
            'fin'=>$fin,
            'cantidad'=>$cantidad,
            'consolidad'=>$consolidad,
            'no_consolidado'=>$no_consolidado,
        ],200);
    }

    public function importar(Request $request){
        $id_sede = $request->route('id_sede');
        $user = Auth::user();
        $salida = [];
        $no_consolidado = [];
        $inicio = Carbon::now();
        if($request->hasFile('archivo')){
            $array = (new AlumnoImport)->toCollection($request->file('archivo'))[0];
            $rows = AlumnoController::importar_alumno($id_sede,$array);
            foreach ($rows as $row) {
                $id_alumno = $row['id_alumno'];
                $id_carrera = $row['id_carrera'];
                $id_tecnicatura = $row['id_tecnicatura']??0;
                $id_plan_estudio = $row['id_plan_estudio'];
                $id_inscripcion = $row['id_inscripcion'];
                $id_modalidad = $row['id_modalidad'];
                $id_beca = $row['id_beca'];
                $cuota_monto = $row['cuota'];
                $matricula_monto = $row['matricula'];
                $anio = $row['anio'];
                if (is_null($id_alumno) or is_null($id_carrera) or is_null($id_inscripcion) or is_null($id_beca) or is_null($id_modalidad) or is_null($id_plan_estudio)) {
                    $no_consolidado[]= $row;
                } else {
                    if($id_alumno > 0){
                        $alumno = Alumno::find($id_alumno);
                        $alumno->id_tipo_alumno_estado = 2;
                        $alumno->save();
                    } else {
                        $alumno = new Alumno;
                        $alumno->nombre = $row['nombre'];
                        $alumno->apellido = $row['apellido'];
                        $alumno->documento = $row['documento'];
                        if($row['fecha_nacimiento']??null){
                            $alumno->fecha_nacimiento = Carbon::parse($row['fecha_nacimiento']);
                        }
                        if($row['provincia']??null){
                            $provincia = Provincia::where('nombre','like','%'.$row['provincia'].'%')->first();
                            if($provincia){
                                $alumno->id_provincia = $provincia->id;
                            } else {
                                $alumno->id_provincia = 0;
                            }
                        }
                        $alumno->domicilio = $row['calle']??null;
                        $alumno->numero = $row['numero']??null;
                        $alumno->piso = $row['piso']??null;
                        $alumno->depto = $row['depto']??null;
                        $alumno->localidad = $row['localidad']??null;
                        $alumno->codigo_postal = $row['codigo_postal']??null;
                        $alumno->telefono = $row['telefono']??null;
                        $alumno->celular = $row['celular']??null;
                        $alumno->ciudad_nacimiento = $row['ciudad_nacimiento']??null;
                        $alumno->nacionalidad = $row['nacionalidad']??null;
                        $alumno->email = $row['email']??null;

                        $alumno->sed_id = $id_sede;
                        $alumno->id_usuario = $user->id;
                        $alumno->id_tipo_alumno_estado = 2;
                        $alumno->save();
                        $id_alumno = $alumno->id;
                    }

                    if($id_inscripcion==0){
                        $beca = Beca::find($id_beca);
                        $todo = new Inscripcion;
                        $todo->id_alumno = $id_alumno;
                        $todo->id_carrera = $id_carrera;
                        $todo->id_plan_estudio = $id_plan_estudio;
                        $todo->id_sede = $id_sede;
                        $todo->anio = $anio;
                        $todo->id_usuario = $user->id;
                        $todo->id_modalidad = $id_modalidad;
                        if($beca){
                            $todo->id_beca = $beca->id;
                            $todo->beca_nombre = $beca->nombre;
                            $todo->beca_porcentaje = $beca->porcentaje;
                        }
                        if($id_tecnicatura>0){
                            $todo->id_tecnicatura = $id_tecnicatura;
                        }
                        if(isset($row['estado'])){
                            $estado = $row['estado'];
                            if($estado){
                                $estado = TipoInscripcionEstado::where('nombre','like','%'.$estado.'%')->first();
                                if($estado){
                                    $todo->id_tipo_inscripcion_estado = $estado->id;
                                }
                            }

                        }
                        $todo->observaciones = $row['observaciones'];
                        $todo->save();

                        
                        $plan_pago = new PlanPago;
                        $plan_pago->id_sede = $id_sede;
                        $plan_pago->id_inscripcion = $todo->id;
                        $plan_pago->matricula_monto = $matricula_monto;
                        $plan_pago->matricula_saldo = $matricula_monto;
                        $plan_pago->cuota_monto = $cuota_monto;
                        $plan_pago->interes_monto = 0;
                        $plan_pago->anio = 2018;
                        $plan_pago->id_usuario = $user->id;
                        $plan_pago->save();
                        $detalle = AlumnoController::preparar_obligaciones(2018,$matricula_monto,$cuota_monto,$beca->porcentaje);
                        $primero = true;
                        $obligaciones = [];
                        foreach ($detalle['obligaciones'] as $obligacion) {
                            if($primero){
                                $primero = false;
                                $matricula = new Obligacion;
                                $matricula->id_plan_pago = $plan_pago->id;
                                $matricula->id_tipo_obligacion = 10;
                                $matricula->descripcion = $obligacion['descripcion'];
                                $matricula->monto = $obligacion['monto'];
                                $matricula->saldo = $obligacion['saldo'];
                                $matricula->fecha = $obligacion['fecha'];
                                $matricula->id_usuario = $user->id;
                                $matricula->fecha_vencimiento = $obligacion['fecha_vencimiento'];
                                $matricula->save();
                                $obligaciones[]=$matricula;
                            } else {
                                $cuota = new Obligacion;
                                $cuota->id_plan_pago = $plan_pago->id;
                                $cuota->id_tipo_obligacion = 1;
                                $cuota->descripcion = $obligacion['descripcion'];
                                $cuota->monto = $obligacion['monto'];
                                $cuota->saldo = $obligacion['saldo'];
                                $cuota->fecha = $obligacion['fecha'];
                                $cuota->fecha_vencimiento = $obligacion['fecha_vencimiento'];
                                $cuota->id_usuario = $user->id;
                                $cuota->save();
                                $obligaciones[]=$cuota;
                            }
                        }

                        //matricula
                        $id = $obligaciones[0]->id;
                        $pago_matricula = $row['pago_matricula'];
                        if($pago_matricula>0){
                            AlumnoController::pagar_cuota($id,$plan_pago->id,$user->id,$id_sede,$pago_matricula,"Pago matricula",2);
                        }

                        $id = $obligaciones[1]->id;
                        $pago_cuota = $row['pago_cuota_1'];
                        if($pago_matricula>0){
                            AlumnoController::pagar_cuota($id,$plan_pago->id,$user->id,$id_sede,$pago_cuota,"Pago cuota 1");
                        }
                        $id = $obligaciones[2]->id;
                        $pago_cuota = $row['pago_cuota_2'];
                        if($pago_matricula>0){
                            AlumnoController::pagar_cuota($id,$plan_pago->id,$user->id,$id_sede,$pago_cuota,"Pago cuota 2");
                        }
                        $id = $obligaciones[3]->id;
                        $pago_cuota = $row['pago_cuota_3'];
                        if($pago_matricula>0){
                            AlumnoController::pagar_cuota($id,$plan_pago->id,$user->id,$id_sede,$pago_cuota,"Pago cuota 3");
                        }
                        $id = $obligaciones[4]->id;
                        $pago_cuota = $row['pago_cuota_4'];
                        if($pago_matricula>0){
                            AlumnoController::pagar_cuota($id,$plan_pago->id,$user->id,$id_sede,$pago_cuota,"Pago cuota 4");
                        }
                        $id = $obligaciones[5]->id;
                        $pago_cuota = $row['pago_cuota_5'];
                        if($pago_matricula>0){
                            AlumnoController::pagar_cuota($id,$plan_pago->id,$user->id,$id_sede,$pago_cuota,"Pago cuota 5");
                        }
                        $id = $obligaciones[6]->id;
                        $pago_cuota = $row['pago_cuota_6'];
                        if($pago_matricula>0){
                            AlumnoController::pagar_cuota($id,$plan_pago->id,$user->id,$id_sede,$pago_cuota,"Pago cuota 6");
                        }
                        $id = $obligaciones[7]->id;
                        $pago_cuota = $row['pago_cuota_7'];
                        if($pago_matricula>0){
                            AlumnoController::pagar_cuota($id,$plan_pago->id,$user->id,$id_sede,$pago_cuota,"Pago cuota 7");
                        }
                        $id = $obligaciones[8]->id;
                        $pago_cuota = $row['pago_cuota_8'];
                        if($pago_matricula>0){
                            AlumnoController::pagar_cuota($id,$plan_pago->id,$user->id,$id_sede,$pago_cuota,"Pago cuota 8");
                        }
                        $id = $obligaciones[9]->id;
                        $pago_cuota = $row['pago_cuota_9'];
                        if($pago_matricula>0){
                            AlumnoController::pagar_cuota($id,$plan_pago->id,$user->id,$id_sede,$pago_cuota,"Pago cuota 9");
                        }
                        $id = $obligaciones[10]->id;
                        $pago_cuota = $row['pago_cuota_10'];
                        if($pago_matricula>0){
                            AlumnoController::pagar_cuota($id,$plan_pago->id,$user->id,$id_sede,$pago_cuota,"Pago cuota 10");
                        }

                        CuentaCorrienteFunction::armar($id_sede,$plan_pago->id);
                        
                    } else {
                        $todo = Inscripcion::find($id_inscripcion);
                    }
                    $salida[] = $todo;
                }
            }
        }
        $cantidad = count($salida);
        $fin = Carbon::now();
        return response()->json([
            'inicio'=>$inicio,
            'fin'=>$fin,
            'cantidad'=>$cantidad,
            'consolidado' => $salida,
            'no_consolidado' => $no_consolidado,
        ],200);
    }

    public static function pagar_cuota($id_obligacion,$id_plan_pago,$id_usuario,$id_sede,$monto,$descripcion,$id_tipo_movimiento = 1){
        $sede = Sede::find($id_sede);
        $fecha = Carbon::now();
        $obligacion = new Obligacion;
        $obligacion->monto = $monto;
        $obligacion->descripcion = $descripcion;
        $obligacion->saldo = 0;
        $obligacion->fecha = $fecha->toDateString();
        $obligacion->fecha_vencimiento = $fecha->toDateString();
        $obligacion->ppa_id = $id_plan_pago;
        $obligacion->tob_id = 3;
        $obligacion->id_usuario = $id_usuario;
        $obligacion->save();

        $todo = new Movimiento;
        $todo->monto = $monto;
        $todo->fecha = Carbon::parse($fecha);
        $todo->descripcion = $descripcion;
        $todo->id_forma_pago = 1;
        $todo->id_tipo_movimiento = $id_tipo_movimiento;
        $todo->id_sede = $id_sede;
        $todo->id_usuario = $id_usuario;
        $todo->id_tipo_egreso_ingreso = 1;
        $todo->save();
        $id_movimiento = $todo->id;

        $numero = $sede->pago_numero + 1;
        $pago = new Pago;
        $pago->fecha = $fecha->toDateString();
        $pago->monto = $monto;
        $pago->descripcion = $descripcion;
        $pago->id_usuario = $id_usuario;
        $pago->ppa_id = $id_plan_pago;
        $pago->obl_id = $obligacion->obl_id;
        $pago->id_sede = $id_sede;
        $pago->id_movimiento = $id_movimiento;
        $pago->numero = $numero;
        $pago->save();
        $sede->pago_numero = $numero;
        $sede->save();

        $parcial = new ObligacionPago;
        $parcial->opa_monto = $monto;
        $parcial->obl_id = $id_obligacion;
        $parcial->pag_id = $pago->pag_id;
        $parcial->id_usuario = $id_usuario;
        $parcial->save();

        $obligacion = Obligacion::find($id_obligacion);
        $saldo = $obligacion->saldo - $monto;
        if($saldo<0){
            $saldo = 0;
        }
        $obligacion->saldo = $saldo;
        $obligacion->save();
    }

    public function importar_alumno($id_sede,$array){
        $salida = [];
        foreach ($array as $item) {
            $errores = [];
            $fecha_nacimiento = $item['fecha_nacimiento']??null;
            if($fecha_nacimiento){
                $fecha_nacimiento = Carbon::createFromFormat("d/m/Y",$fecha_nacimiento);
            }
            $documento = $item['documento'];
            $apellido = trim($item['apellido']);
            $nombre = trim($item['nombre']);
            $anio = $item['anio'];
            $carrera = $item['carrera'];
            $tecnicatura = $item['tecnicatura']??"";
            $modalidad = $item['modalidad']??"";
            $beca = $item['beca']??"";
            $matricula = floatval($item['matricula']??0);
            $cuota = floatval($item['cuota']??0);
            $pago_matricula = floatval($item['pago_matricula']??0);
            $pago_cuota_1 = floatval($item['pago_cuota_1']??0);
            $pago_cuota_2 = floatval($item['pago_cuota_2']??0);
            $pago_cuota_3 = floatval($item['pago_cuota_3']??0);
            $pago_cuota_4 = floatval($item['pago_cuota_4']??0);
            $pago_cuota_5 = floatval($item['pago_cuota_5']??0);
            $pago_cuota_6 = floatval($item['pago_cuota_6']??0);
            $pago_cuota_7 = floatval($item['pago_cuota_7']??0);
            $pago_cuota_8 = floatval($item['pago_cuota_8']??0);
            $pago_cuota_9 = floatval($item['pago_cuota_9']??0);
            $pago_cuota_10 = floatval($item['pago_cuota_10']??0);
            $observaciones = $item['observaciones'];

            $id_alumno = 0;
            if(!empty($apellido) and !empty($nombre)){
                $documento = intval($documento);
                if($documento>0){
                    $alumno = Alumno::where([
                        'sed_id' => $id_sede,
                        'estado' => 1,
                    ])->where('documento',$documento)->first();
                    if($alumno){
                        $id_alumno = $alumno->id;
                    }
                }
            } else {
                $id_alumno = null;
                $error = "Los datos del alumno son insuficientes.";
                $errores[]=$error;
            }

            $id_modalidad = null;
            $id_plan_estudio = null;
            if(!empty($carrera)){
                $carrera_obj = Carrera::where('estado',1)
                    ->where(function($q)use($carrera){
                        return $q->where('nombre','like','%'.$carrera.'%')
                            ->orWhere('nombre_corto','like','%'.$carrera.'%');
                    })
                    ->first();
                if($carrera_obj){
                    $id_carrera = $carrera_obj->id;
                    $id_plan_estudio = $carrera_obj->id_plan_estudio??null;
                    if(isset($row['ed'])){
                        $ed = $row['ed'];
                        if($ed == 1){
                            $plan_estudio = PlanEstudio::where([
                                'estado' => 1,
                                'car_id' => $id_carrera,
                            ])->orderBy('anio','desc')->first();
                        } else {
                            $plan_estudio = PlanEstudio::where([
                                'estado' => 1,
                                'car_id' => $id_carrera,
                            ])->orderBy('anio','asc')->first();
                        }
                        if($plan_estudio){
                            $id_plan_estudio = $plan_estudio->id;
                        }
                    }
                    if(!empty($modalidad)){
                        $modalidad_obj = Modalidad::where('estado',1)->where('nombre','like','%'.$modalidad.'%')->first();
                        if($modalidad_obj){
                            $id_modalidad = $modalidad_obj->id;
                        }
                    } elseif(!empty($carrera_obj->modalidades)){
                        $id_modalidad = $carrera_obj->modalidades[0]->modalidad->id;
                    }
                } else {
                    $id_carrera = null;
                    $error = "La carrera no coincide con ninguna.";
                    $errores[]=$error;
                }
            } else {
                $id_carrera = null;
                $error = "Los datos de la carrera son insuficientes.";
                $errores[]=$error;
            }
            $id_tecnicatura = null;
            if(!empty($tecnicatura)){
                $carrera_obj = Carrera::where('estado',1)
                    ->where(function($q)use($tecnicatura){
                        return $q->where('nombre','like','%'.$tecnicatura.'%')
                            ->orWhere('nombre_corto','like','%'.$tecnicatura.'%');
                    })
                    ->first();
                if($carrera_obj){
                    $id_tecnicatura = $carrera_obj->id;
                } else {
                    $id_tecnicatura = null;
                    $error = "La tecnicatura no coincide con ninguna.";
                    $errores[]=$error;
                }
            }

            if(!empty($beca)){
                $beca_obj =  Beca::where('estado',1)->where('nombre','like','%'.$beca.'%')->first();
                if($beca_obj){
                    $id_beca = $beca_obj->id;
                } else {
                    $id_beca = 1;
                    $error = "La beca no coincide con ninguna.";
                    $errores[]=$error;
                }
            } else {
                $id_beca = 1;
                $error = "Los datos de la beca son insuficientes.";
                $errores[]=$error;
            }

            if(is_null($id_alumno) or is_null($id_carrera)){
                $error = "La inscripcion no puede realizarse.";
                $errores[]=$error;
                $id_inscripcion = null;
            } elseif ($id_alumno > 0) {
                $inscripcion = Inscripcion::where([
                    'estado' => 1,
                    'sed_id' => $id_sede,
                    'car_id' => $id_carrera,
                    'alu_id' => $id_alumno,
                ])->first();
                if($inscripcion){
                    $id_inscripcion = $inscripcion->id;
                } else {
                    $id_inscripcion = 0;
                }
                
            } else {
                $id_inscripcion = 0;
            }

            if(empty($observaciones)){
                $item['observaciones'] = "";
            }

            $item['apellido'] = $apellido;
            $item['nombre'] = $nombre;
            $item['matricula'] = $matricula;
            $item['cuota'] = $cuota;
            $item['pago_matricula'] = $pago_matricula;
            $item['pago_cuota_1'] = $pago_cuota_1;
            $item['pago_cuota_2'] = $pago_cuota_2;
            $item['pago_cuota_3'] = $pago_cuota_3;
            $item['pago_cuota_4'] = $pago_cuota_4;
            $item['pago_cuota_5'] = $pago_cuota_5;
            $item['pago_cuota_6'] = $pago_cuota_6;
            $item['pago_cuota_7'] = $pago_cuota_7;
            $item['pago_cuota_8'] = $pago_cuota_8;
            $item['pago_cuota_9'] = $pago_cuota_9;
            $item['pago_cuota_10'] = $pago_cuota_10;
            $item['id_alumno'] = $id_alumno;
            $item['id_carrera'] = $id_carrera;
            $item['id_tecnicatura'] = $id_tecnicatura;
            $item['id_plan_estudio'] = $id_plan_estudio;
            $item['id_modalidad'] = $id_modalidad;
            $item['id_beca'] = $id_beca;
            $item['id_inscripcion'] = $id_inscripcion;
            $item['errores'] = $errores;

            if($fecha_nacimiento){
                $item['fecha_nacimiento']=$fecha_nacimiento;
            }

            $salida[]= $item;
        }
        return $salida;
    }

    public function password(Request $request)
    {
        $id_alumno = $request->route('id_alumno');
        $validator = Validator::make($request->all(),[
            'password' => 'required',
        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()],403);
        }
        $alumno = Alumno::find($id_alumno);
        $alumno->alu_password = bcrypt($request->input('password'));
        $alumno->save();

        return response()->json($alumno,200);
    }
}

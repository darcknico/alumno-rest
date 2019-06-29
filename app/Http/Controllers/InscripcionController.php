<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Carrera;
use App\Models\Beca;
use App\Models\Inscripcion;
use App\Models\PlanPago;
use App\Models\Pago;
use App\Models\Movimiento;
use App\Models\Obligacion;
use App\Models\ObligacionInteres;
use App\Models\ObligacionPago;
use App\Models\Comision;
use App\Models\ComisionAlumno;
use App\Models\Comision\ExamenAlumno;
use App\Models\AsistenciaAlumno;
use App\Models\TipoInscripcionEstado;

use App\Functions\DiariaFunction;

use App\Exports\InscripcionExport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use Carbon\Carbon;
use JasperPHP\JasperPHP; 

class InscripcionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $id_sede = $request->route('id_sede');
        $id_departamento = $request->route('id_departamento',null);
        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);
        $registros = Inscripcion::with([
            'alumno.tipoDocumento',
            'usuario',
            'carrera.departamento',
            'plan_estudio',
            'tipo_estado',
            ])->where([
            'sed_id' => $id_sede,
            'estado' => 1,
            ]);
        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros->orderBy('created_at','desc')
            ->get();
            return response()->json($todo,200);
        }
        $id_departamento = $request->query('id_departamento',0);
        $id_carrera = $request->query('id_carrera',0);
        $id_beca = $request->query('id_beca',0);
        $id_tipo_inscripcion_estado = $request->query('id_tipo_inscripcion_estado',0);
        $anio_inicial = $request->query('anio_inicial',0);
        $anio_final = $request->query('anio_final',0);

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
            ->when($id_beca>0,function($q)use($id_beca){
                return $q->where('bec_id',$id_beca);
            })
            ->when($id_tipo_inscripcion_estado>0,function($q)use($id_tipo_inscripcion_estado){
                return $q->where('tie_id',$id_tipo_inscripcion_estado);
            })
            ->when($anio_inicial>0,function($q)use($anio_inicial){
                return $q->where('anio','>=',$anio_inicial);
            })
            ->when($anio_final>0,function($q)use($anio_final){
                return $q->where('anio','<=',$anio_final);
            });
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
              if(strlen($value)>0){
                $registros = $registros->where(function($query) use  ($value,$id_sede) {
                  $query
                    ->whereIn('car_id',function($q)use($value,$id_sede){
                        $q->select('car_id')->from('tbl_carreras')
                        ->where('sed_id',$id_sede)
                        ->where(function($qt) use  ($value){
                            $qt->where('car_nombre','like','%'.$value.'%')
                            ->orWhere('car_nombre_corto','like','%'.$value.'%');
                        });
                    })
                    ->orWhereIn('alu_id',function($q)use($value,$id_sede){
                        $q->select('alu_id')->from('tbl_alumnos')
                        ->where('sed_id',$id_sede)
                        ->where(function($qt) use  ($value){
                            $qt->where('alu_nombre','like','%'.$value.'%')
                            ->orWhere('alu_apellido','like','%'.$value.'%')
                            ->orWhere('alu_documento',$value);
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

    public function exportar(Request $request){
        $id_sede = $request->route('id_sede');
        $search = $request->query('search','');
        $id_departamento = $request->query('id_departamento',0);
        $id_carrera = $request->query('id_carrera',0);
        $id_beca = $request->query('id_beca',0);
        $id_tipo_inscripcion_estado = $request->query('id_tipo_inscripcion_estado',0);
        $anio_inicial = $request->query('anio_inicial',0);
        $anio_final = $request->query('anio_final',0);

        $fecha = Carbon::now()->format('d.m.Y');

        return (new InscripcionExport(
            $id_sede,
            $search,
            $id_departamento,
            $id_carrera,
            $id_beca,
            $id_tipo_inscripcion_estado,
            $anio_inicial,
            $anio_final
        ))->download('inscripciones'.$fecha.'.xlsx');
    }

    public function estadisticas(Request $request){
        $id_sede = $request->route('id_sede');
        $totales = \DB::table('tbl_inscripciones')
        ->selectRaw('
            sum(if(tie_id=1,1,0)) as regular,
            sum(if(tie_id=2,1,0)) as egresado,
            sum(if(tie_id=3,1,0)) as abandonado
            ')
        ->where([
            'estado' => 1,
            'sed_id' => $id_sede,
        ])
        ->groupBy('sed_id')
        ->first();
        if(!$totales){
            $totales['regular'] = 0;
            $totales['egresado'] = 0;
            $totales['abandonado'] = 0;
        }

        $totales_hoy = \DB::table('tbl_inscripciones')
        ->selectRaw('
            sum(if(tie_id=1,1,0)) as regular,
            sum(if(tie_id=2,1,0)) as egresado,
            sum(if(tie_id=3,1,0)) as abandonado
            ')
        ->where([
            'estado' => 1,
            'sed_id' => $id_sede,
        ])
        ->whereYear('created_at',Carbon::now()->year)
        ->groupBy('sed_id')
        ->first();
        if(!$totales_hoy){
            $totales_hoy['regular'] = 0;
            $totales_hoy['egresado'] = 0;
            $totales_hoy['abandonado'] = 0;
        }
        return response()->json([
            'totales' => $totales,
            'totales_hoy' => $totales_hoy,
        ], 200);
    }

    public function show(Request $request){
        $id_sede = $request->route('id_sede');
        $id_inscripcion = $request->route('id_inscripcion');
        $todo = Inscripcion::with([
            'alumno.tipoDocumento',
            'usuario',
            'carrera.departamento',
            'alumno.tipoDocumento',
            'alumno.provincia',
            'alumno.tipo_civil',
            'plan_estudio',
            'planes_pago' => function($q){
                $q->where('estado',1);
            },
            'tipo_estado',
            'modalidad',
            ])->where([
            'sed_id' => $id_sede,
            'ins_id' => $id_inscripcion,
            'estado' => 1,
            ])->first();
        return response()->json($todo,200);
    }

    public function asistencias(Request $request){
        $id_inscripcion = $request->route('id_inscripcion');
        $inscripcion = Inscripcion::find($id_inscripcion);
        $todo = AsistenciaAlumno::with('asistencia.comision.materia','tipo')
            ->where('estado',1)
            ->where('id_alumno',$inscripcion->id_alumno)
            ->get()
            ->sortBy(function($useritem, $key) {
                return $useritem->asistencia->fecha;
            });
        return response()->json($todo,200);
    }

    public function examenes(Request $request){
        $id_inscripcion = $request->route('id_inscripcion');
        $inscripcion = Inscripcion::find($id_inscripcion);
        $todo = ExamenAlumno::with('examen.comision.materia','tipo')
            ->where('estado',1)
            ->where('id_alumno',$inscripcion->id_alumno)
            ->get()
            ->sortBy(function($useritem, $key) {
                return $useritem->examen->fecha;
            });
        return response()->json($todo,200);
    }

    public function estado_deuda(Request $request){
        $id_inscripcion = $request->route('id_inscripcion');

        $planes_pago = PlanPago::where('estado',1)->where('id_inscripcion',$id_inscripcion)->orderBy('anio','asc')->get();
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

    public function update(Request $request){
        $id_sede = $request->route('id_sede');
        $id_inscripcion = $request->route('id_inscripcion');
        $validator = Validator::make($request->all(),[
            'anio' => 'required',
            'id_carrera' => 'required',
            'id_plan_estudio' => 'required',
            'id_modalidad' => 'required',
            'id_beca' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $anio = $request->input('anio');
        $id_carrera = $request->input('id_carrera');
        $id_plan_estudio = $request->input('id_plan_estudio');
        $id_modalidad = $request->input('id_modalidad');
        $id_beca = $request->input('id_beca');
        $observaciones = $request->input('observaciones',null);
        $fecha_egreso = $request->input('fecha_egreso',null);
        $beca = Beca::find($id_beca);

        $inscripcion = Inscripcion::find($id_inscripcion);
        $inscripcion->anio = $anio;
        $inscripcion->id_carrera = $id_carrera;
        $inscripcion->id_plan_estudio = $id_plan_estudio;
        $inscripcion->id_beca = $id_beca;
        $inscripcion->beca_nombre = $beca->nombre;
        $inscripcion->beca_porcentaje = $beca->porcentaje;
        $inscripcion->observaciones = $observaciones;
        $inscripcion->fecha_egreso = $fecha_egreso;
        $inscripcion->save();

        return response()->json($inscripcion,200);
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();
        $id_inscripcion = $request->route('id_inscripcion');

        $inscripcion = Inscripcion::find($id_inscripcion);
        $inscripcion->estado = 0;
        $inscripcion->save();

        $planes_pago = PlanPago::where([
            'ins_id' => $id_inscripcion,
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
        
        $comisiones = ComisionAlumno::where([
            'ins_id' => $id_inscripcion,
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
        return response()->json($inscripcion,200);
    }

    public function planes_pago(Request $request){
        $id_inscripcion = $request->route('id_inscripcion');

        $planes_pago = PlanPago::where([
            'estado' => 1,
            'ins_id' => $id_inscripcion,
        ])->orderBy('anio','desc')
        ->get();

        return response()->json($planes_pago,200);
    }

    public function pagos(Request $request){
        $id_inscripcion = $request->route('id_inscripcion');
        $todo = Pago::with('tipo','usuario','movimiento.forma')->where('id_inscripcion',$id_inscripcion)->orderBy('created_at','desc')->get();
        return response()->json($todo,200);
    }

    public function estado(Request $request){
        $id_inscripcion = $request->route('id_inscripcion');

        $validator = Validator::make($request->all(),[
            'id_tipo_inscripcion_estado' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $id_tipo_inscripcion_estado = $request->input('id_tipo_inscripcion_estado');
        $estado = TipoInscripcionEstado::find($id_tipo_inscripcion_estado);
        if(!$estado){
          return response()->json(['error'=>'El estado no existe.'],403);
        }

        $inscripcion = Inscripcion::find($id_inscripcion);
        $inscripcion->id_tipo_inscripcion_estado = $id_tipo_inscripcion_estado;
        $inscripcion->save();
        return response()->json($inscripcion,200);
    }

    public function carreras_alumnos(Request $request)
    {
        $user = Auth::user();
        $id_sede = $request->route('id_sede');
        $id_carrera = $request->route('id_carrera');
        $id_tipo_inscripcion_estado = $request->query('id_tipo_inscripcion_estado',1);

        $todo = Inscripcion::with('alumno')->where([
            'sed_id' => $id_sede,
            'car_id' => $id_carrera,
            'estado' => 1,
            'tie_id' => $id_tipo_inscripcion_estado,
        ])->get();
        return response()->json($todo,200);
    }

    public function tipos_estado(Request $request){
        $todo = TipoInscripcionEstado::where('estado',1)->get();
        return response()->json($todo,200);
    }


    public function reporte_ficha(Request $request){
        $id_sede = $request->route('id_sede');
        $id_inscripcion = $request->route('id_inscripcion');
        $inscripcion = Inscripcion::find($id_inscripcion);

        $jasper = new JasperPHP;
        $input = storage_path("app/reportes/alumno_inscripcion.jasper");
        $output = storage_path("app/reportes/".uniqid());
        $ext = "pdf";

        $jasper->process(
            $input,
            $output,
            [$ext],
            [
                'id_inscripcion' => $id_inscripcion,
                'header'=> storage_path("app/images/header.png")??null,
                'footer'=> storage_path("app/images/footer.png")??null,
                'REPORT_LOCALE' => 'es_AR',
            ],
            \Config::get('database.connections.mysql')
        )->execute();
        
        $filename ='ficha_inscripcion-'.$inscripcion->alumno->documento;
        /*
        //header('Access-Control-Allow-Origin: *');
        header('Content-Description: application/pdf');
        header('Content-Type: application/pdf');
        header('Content-Disposition:attachment; filename=' . $filename . '.' . $ext);
        readfile($output . '.' . $ext);
        unlink($output. '.'  . $ext);
        flush();
        */
        return response()->download($output . '.' . $ext, $filename,['Content-Type: application/pdf'])->deleteFileAfterSend();
    }

    public function reporte_constancia_regular(Request $request){
        $id_sede = $request->route('id_sede');
        $id_inscripcion = $request->route('id_inscripcion');
        $inscripcion = Inscripcion::find($id_inscripcion);

        $jasper = new JasperPHP;
        $input = storage_path("app/reportes/alumno_inscripcion_constancia.jasper");
        $output = storage_path("app/reportes/".uniqid());
        $ext = "pdf";

        $jasper->process(
            $input,
            $output,
            [$ext],
            [
                'id_inscripcion' => $id_inscripcion,
                'logo'=> storage_path("app/images/logo_constancia.png")??null,
                'REPORT_LOCALE' => 'es_AR',
            ],
            \Config::get('database.connections.mysql')
        )->execute();
        
        $filename ='constancia_alumno_regular-'.$inscripcion->alumno->documento;
        /*
        //header('Access-Control-Allow-Origin: *');
        header('Content-Description: application/pdf');
        header('Content-Type: application/pdf');
        header('Content-Disposition:attachment; filename=' . $filename . '.' . $ext);
        readfile($output . '.' . $ext);
        unlink($output. '.'  . $ext);
        flush();
        */
        return response()->download($output . '.' . $ext, $filename,['Content-Type: application/pdf'])->deleteFileAfterSend();
    }
}

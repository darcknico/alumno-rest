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
use App\Models\Academico\InscripcionAbandono;

use App\Functions\DiariaFunction;
use App\Filters\InscripcionFilter;

use App\Exports\InscripcionExport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use \DB;

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
        $registros = InscripcionFilter::index($request,$registros);
        
        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros->orderBy('created_at','desc')
            ->get();
            return response()->json($todo,200);
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
        $fecha_inicial = $request->query('fecha_inicial',"");
        $fecha_final = $request->query('fecha_final',"");

        $fecha = Carbon::now()->format('d.m.Y');

        return (new InscripcionExport(
            $id_sede,
            [
                'search' => $search,
                'id_departamento' => $id_departamento,
                'id_carrera' => $id_carrera,
                'id_beca' => $id_beca,
                'id_tipo_inscripcion_estado' => $id_tipo_inscripcion_estado,
                'anio_inicial' => $anio_inicial,
                'anio_final' => $anio_final,
                'fecha_inicial' => $fecha_inicial,
                'fecha_final' => $fecha_final,
            ]
        ))->download('inscripciones'.$fecha.'.xlsx');
    }

    public function estadisticas(Request $request){
        $id_sede = $request->route('id_sede');
        $totales = DB::table('tbl_inscripciones')
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

        $totales_hoy = DB::table('tbl_inscripciones')
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

        $planes_pago = PlanPago::where([
            'ins_id' => $id_inscripcion,
            'estado' => 1,
        ])->get();
        if(count($planes_pago)>0){
            return response()->json(['error'=>'La inscripción posee planes de pago activas, no puede ser eliminado.'],403);
        }
        /*
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
        */
        $inscripcion = Inscripcion::find($id_inscripcion);
        $inscripcion->estado = 0;
        $inscripcion->save();
        return response()->json($inscripcion,200);
    }

    public function planes_pago(Request $request){
        $id_inscripcion = $request->route('id_inscripcion');

        $planes_pago = PlanPago::where([
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
            'fecha_egreso' => 'nullable | date',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $id_tipo_inscripcion_estado = $request->input('id_tipo_inscripcion_estado');
        $fecha_egreso = $request->input('fecha_egreso');
        $estado = TipoInscripcionEstado::find($id_tipo_inscripcion_estado);
        if(!$estado){
          return response()->json(['error'=>'El estado no existe.'],403);
        }

        $inscripcion = Inscripcion::find($id_inscripcion);
        $inscripcion->id_tipo_inscripcion_estado = $id_tipo_inscripcion_estado;
        if($id_tipo_inscripcion_estado == 2){
            $inscripcion->fecha_egreso = $fecha_egreso;
        } else {
            $inscripcion->fecha_egreso = null;
        }
        $inscripcion->save();
        if($id_tipo_inscripcion_estado == 1 or $id_tipo_inscripcion_estado == 2){
            InscripcionAbandono::where('estado',1)
                ->where('id_inscripcion',$id_inscripcion)
            ->update([
            'estado' => 0,
            ]);
        }
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
        return response()->download($output . '.' . $ext, $filename,['Content-Type: application/pdf'])->deleteFileAfterSend();
    }
    public function reporte_constancia_cursadas(Request $request){
        $id_sede = $request->route('id_sede');
        $id_inscripcion = $request->route('id_inscripcion');
        $inscripcion = Inscripcion::find($id_inscripcion);

        $jasper = new JasperPHP;
        $input = storage_path("app/reportes/alumno_inscripcion_cursadas.jasper");
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
        
        $filename ='constancia_materias_cursadas-'.$inscripcion->alumno->documento;
        return response()->download($output . '.' . $ext, $filename,['Content-Type: application/pdf'])->deleteFileAfterSend();
    }

    public function estadisticas_rendimientos(Request $request){
        $id_sede = $request->route('id_sede');
        $id_inscripcion = $request->route('id_inscripcion');
        $anio = $request->query('anio',null);

        $inscripcion = Inscripcion::find($id_inscripcion);

        if(is_null($anio)){
            $anio = Carbon::now()->year;
        }
        $current_date = $anio.'-1-1';
        $sql = "
            SELECT YEAR(d.date) as anio,
                MONTH(d.date) as mes,
                COALESCE(count(cae.cae_nota),0) as cantidad,
                COALESCE(AVG(cae.cae_nota),0) as total
                FROM (SELECT ? + INTERVAL seq month AS date 
                    FROM seq_0_to_11 AS offs
                ) d LEFT OUTER JOIN
                tbl_comision_examen cex
                ON MONTH(d.date )= MONTH(cex.cex_fecha) 
                    AND YEAR(d.date) = YEAR(cex.cex_fecha)
                    AND cex.estado = 1
                left join 
                    tbl_comisiones com on cex.com_id = com.com_id
                    AND com.estado = 1
                    AND com.sed_id = ?
                left join 
                    tbl_comision_alumno cal on cal.com_id = com.com_id 
                    AND cal.ins_id = ?
                left join 
                    tbl_comision_examen_alumno cae on cae.cex_id = cex.cex_id AND cae.alu_id = cal.alu_id
            group by 1,2
            order by 1,2
                ";
        $notas = DB::select($sql, [
            $current_date,
            $id_sede,
            $id_inscripcion,
        ]);

        $sql = "
            SELECT YEAR(d.date) as anio,
                MONTH(d.date) as mes,
                COALESCE(count(amn.amn_nota),0) as cantidad,
                COALESCE(AVG(amn.amn_nota),0) as total
                FROM (SELECT ? + INTERVAL seq month AS date 
                    FROM seq_0_to_11 AS offs
                ) d LEFT OUTER JOIN
                tbl_alumno_materia_nota amn
                ON MONTH(d.date )= MONTH(amn.amn_fecha) 
                AND YEAR(d.date) = YEAR(amn.amn_fecha) 
                AND amn.ins_id = ? 
                AND amn.estado = 1
            group by 1,2
            order by 1,2
                ";
        $viejos = DB::select($sql, [
            $current_date,
            $id_inscripcion,
        ]);

        $sql = "
            SELECT YEAR(d.date) as anio,
                MONTH(d.date) as mes,
                COALESCE(count(mam.mam_nota_final),0) as cantidad,
                COALESCE(AVG(mam.mam_nota_final),0) as total
                FROM (SELECT ? + INTERVAL seq month AS date 
                    FROM seq_0_to_11 AS offs
                ) d LEFT OUTER JOIN
                tbl_mesa_materia mma
                ON MONTH(d.date )= MONTH(mma.mma_fecha) 
                AND YEAR(d.date) = YEAR(mma.mma_fecha)
                AND mma.estado = 1
                left join 
                    tbl_mesa_alumno_materia mam on mam.mma_id = mma.mma_id 
                    AND mam.ins_id = ?
                    AND mam.estado = 1
            group by 1,2
            order by 1,2
                ";
        $nuevos = DB::select($sql, [
            $current_date,
            $id_inscripcion,
        ]);

        $mesas = [];
        foreach ($viejos as $index => $viejo) {
            $divisor = 2;
            if($viejo->cantidad == 0 or $nuevos[$index]->cantidad == 0){
                $divisor = 1;
            }
            $mesas[] = [
                'total' => ($viejo->total + $nuevos[$index]->total)/$divisor,
                'cantidad' => ($viejo->cantidad + $nuevos[$index]->cantidad),
                'anio' => $viejo->anio,
                'mes' => $viejo->mes,
            ];
        }

        return response()->json([
            'notas' => $notas,
            'mesas' => $mesas,
        ],200);
    }
}

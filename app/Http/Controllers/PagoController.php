<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Sede;
use App\Models\Movimiento;
use App\Models\Alumno;
use App\Models\PlanPago;
use App\Models\Inscripcion;
use App\Models\Carrera;
use App\Models\Pago;
use App\Models\TipoPago;
use App\Models\Obligacion;
use App\Models\ObligacionPago;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use Carbon\Carbon;
use App\Functions\CuentaCorrienteFunction;
use App\Functions\DiariaFunction;
use App\Functions\PlanPagoFunction;
use App\Functions\ObligacionFunction;
use JasperPHP\JasperPHP; 

use App\Exports\PagoExport;

class PagoController extends Controller
{
	public function index(Request $request){
		$id_sede = $request->route('id_sede');
		$search = $request->query('search','');
		$sort = $request->query('sort','');
		$order = $request->query('order','');
    	$start = $request->query('start',0);
		$length = $request->query('length',0);
		$registros = Pago::with([
			'tipo',
			'usuario',
			'inscripcion.alumno',
			'movimiento.forma',
		])->where([
			'sed_id' => $id_sede,
			'estado' => 1,
		]);
		if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
			$todo = $registros->orderBy('created_at','desc')
			->get();
			return response()->json($todo,200);
		}
		$id_tipo_pago = $request->query('id_tipo_pago',0);
		$id_departamento = $request->query('id_departamento',0);
		$id_carrera = $request->query('id_carrera',0);
		$fecha_inicio = $request->query('fecha_inicio',null);
		$fecha_fin = $request->query('fecha_fin',null);

		$registros = $registros
			->when($id_tipo_pago>0,function($q)use($id_tipo_pago){
				return $q->where('id_tipo_pago',$id_tipo_pago);
			})
			->when($id_departamento>0,function($q)use($id_departamento){
				$carreras = Carrera::where([
					'dep_id' => $id_departamento,
					'estado' => 1,
				])->pluck('car_id')->toArray();
				$inscripciones = Inscripcion::where([
					'estado' => 1,
				])
				->whereIn('car_id',$carreras)
				->pluck('ins_id')->toArray();
				$planes_pago = PlanPago::whereIn('ins_id',$inscripciones)->where('estado',1)
				->pluck('ppa_id')->toArray();
				return $q->whereIn('ppa_id',$planes_pago);
			})
			->when($id_carrera>0,function($q)use($id_carrera){
				$inscripciones = Inscripcion::where([
					'car_id' => $id_carrera,
					'estado' => 1,
				])
				->pluck('ins_id')->toArray();
				$planes_pago = PlanPago::whereIn('ins_id',$inscripciones)->where('estado',1)
				->pluck('ppa_id')->toArray();
				return $q->whereIn('ppa_id',$planes_pago);
			})
			->when(!empty($fecha_inicio),function($q)use($fecha_inicio){
					return $q->whereDate('pag_fecha','>=',$fecha_inicio);
				})
			->when(!empty($fecha_fin),function($q)use($fecha_fin){
				return $q->whereDate('pag_fecha','<=',$fecha_fin);
			});
		$values = explode(" ", $search);
		if(count($values)>0){
			foreach ($values as $key => $value) {
				if(strlen($value)>0){
					$registros = $registros->where(function($query) use  ($value,$id_sede) {
						$query->where('monto', $value)
							->orWhereIn('ppa_id',function($q)use($value,$id_sede){
								$alumnos = Alumno::where([
									'estado' => 1,
									'sed_id' => $id_sede,
								])
								->where('alu_nombre','like','%'.$value.'%')
								->orWhere('alu_apellido','like','%'.$value.'%')
								->pluck('alu_id')->toArray();
								$inscripciones = Inscripcion::where([
									'estado' => 1,
									'sed_id' => $id_sede,
								])->whereIn('alu_id',$alumnos)->pluck('ins_id')->toArray();
								return $q->select('ppa_id')->from('tbl_planes_pago')->where([
									'estado' => 1,
									'sed_id' => $id_sede,
								])->whereIn('ins_id',$inscripciones);
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

		$salida = [];
		foreach ($registros as $registro) {
			if(is_null($registro->inscripcion)){
				$plan_pago = PlanPago::find($registro->id_plan_pago);
				$inscripciones = Inscripcion::find($plan_pago->id_inscripcion);

				$registro['alumno'] = $inscripciones->alumno;
			}
			$salida[]=$registro;
		}

		return response()->json([
			'total_count'=>intval($total_count),
			'items'=>$salida,
		],200);
	}


	public function show(Request $request){
		$id_pago = $request->route('id_pago');
		$pago = Pago::with('detalles.obligacion.tipo','obligacion.tipo','usuario','plan_pago')
		->find($id_pago);
		return response()->json($pago,200);
	}

	public function destroy(Request $request){
        $user = Auth::user();
		$id_sede = $request->route('id_sede');
		$id_pago = $request->route('id_pago');
		$pago_original = Pago::find($id_pago);
		$plan_pago = PlanPago::find($pago_original->id_plan_pago);
		try{
			if($pago_original->estado == 1){
				$pago_original->estado = 0;
				$pago_original->save();
				if (!is_null($pago_original->movimiento)) {
					$movimiento = Movimiento::find($pago_original->id_movimiento);
					$movimiento->estado = 0;
			        $movimiento->deleted_at = Carbon::now();
			        $movimiento->usu_id_baja = $user->id;
					$movimiento->save();

					DiariaFunction::quitar($id_sede,$pago_original->id_movimiento);
				}
				$obligacion_original = Obligacion::where('obl_id',$pago_original->obl_id)->first();
				$obligacion_original->estado = 0;
				$obligacion_original->save();
				$pagos = ObligacionPago::where([
					'pag_id' => $id_pago,
				])->pluck('obl_id')->toArray();
				ObligacionPago::where([
					'pag_id' => $id_pago,
				])->update([
					'estado' => 0,
				]);
				
				$obligaciones = Obligacion::whereIn('obl_id',$pagos)
				->orderByRaw('obl_fecha_vencimiento,obl_id asc')
				->get();
				foreach ($obligaciones as $obligacion) {
					$pago = ObligacionPago::where([
						'pag_id' => $id_pago,
						'obl_id' => $obligacion->obl_id,
					])->first();
					
					$obligacion = Obligacion::find($obligacion->obl_id);
					$obligacion = ObligacionFunction::actualizar($obligacion);
				}
			} else {
				$pago_original->estado = 1;
				$pago_original->save();
				if (!is_null($pago_original->movimiento)) {
					$movimiento = Movimiento::find($pago_original->id_movimiento);
					$movimiento->estado = 1;
					$movimiento->save();
					DiariaFunction::agregar($id_sede,$pago_original->id_movimiento);
				}
				$obligacion_original = Obligacion::where('obl_id',$pago_original->obl_id)->first();
				$obligacion_original->estado = 1;
				$obligacion_original->save();
			}
			
			if($pago_original->id_tipo_pago != 10 and $pago_original->id_tipo_pago != 20){
				CuentaCorrienteFunction::armar($id_sede,$obligacion_original->id_plan_pago,$pago_original->estado == 1);
			}
		} catch(Exception $e){
			return response()->json($e->getMessagge(),401);
		}
    	PlanPagoFunction::actualizar($plan_pago);
		return response()->json($pago_original,200);
	}

	public function estadisticas(Request $request){
		$id_sede = $request->route('id_sede');
    	$tipo_corte = $request->query('tipo_corte',1); //1 DIA - 2 MES
    	$limite = $request->query('limite',7); //1 DIA - 2 MES
    	switch ($tipo_corte) {
    		case 1:
    		$q = " DATE_FORMAT(pag_fecha,'%Y-%m-%d') as fecha, ";
    		break;
    		default:
    		$q = " DATE_FORMAT(pag_fecha,'%Y-%m-1') as fecha, ";
    		break;
    	}
    	$pagos = \DB::table('tbl_pagos')->selectRaw($q."
    		sum(pag_monto) as cuota,
    		")
    	->where([
    		'sed_id' => $id_sede,
    		'estado' => 1,
    	])
    	->whereDate('pag_fecha','<=',Carbon::now())
    	->when($limite>0,function($query)use($limite,$tipo_corte){
    		if($tipo_corte == 1){
    			return $query->whereDate('pag_fecha','>=',Carbon::now()->subDays($limite));
    		} else {
    			return $query->whereDate('pag_fecha','>=',Carbon::now()->subMonth($limite));
    		}
    	})
    	->groupBy('fecha')
    	->orderBy('created_at','asc')
    	->when($limite>0,function($query)use($limite){
    		return $query->limit($limite);
    	})
    	->get();
    	return response()->json(array_reverse($pagos->toArray()),200);
    }
    
    public function estadistica_cuenta_corriente(Request $request){
    	$id_sede = $request->route('id_sede');
    	$tipo_corte = $request->query('tipo_corte',1); //1 DIA - 2 MES
    	$limite = $request->query('limite',7); //1 DIA - 2 MES
    	switch ($tipo_corte) {
    		case 1:
    		$q = " DATE_FORMAT(obl_fecha_vencimiento,'%Y-%m-%d') as fecha, ";
    		break;
    		default:
    		$q = " DATE_FORMAT(obl_fecha_vencimiento,'%Y-%m-1') as fecha, ";
    		break;
    	}
    	$planes = \DB::table('tbl_departamentos')->select('dep_id')
    	->join('tbl_carreras','tbl_departamentos.dep_id','tbl_carreras.dep_id')
    	->join('tbl_inscripciones','tbl_carreras.car_id','tbl_inscripciones.car_id')
    	->join('tbl_planes_pago','tbl_inscripciones.ins_id','tbl_planes_pago.ins_id')
        //->whereIn('tbl_inscripciones.tie_id',[1,2])
    	->where([
    		'tbl_departamentos.sed_id' => $id_sede,
    		'tbl_departamentos.estado' => 1,
    		'tbl_carreras.estado' => 1,
    		'tbl_inscripciones.estado' => 1,
    		'tbl_planes_pago.estado' => 1,
    	])->pluck('ppa_id')->toArray();
    	$todo = \DB::table('tbl_obligaciones')->selectRaw($q."
    		sum(IF(tco_id=1,obl_monto,0)) as cuota_monto,
    		sum(IF(tco_id=1,obl_saldo,0)) as cuota_saldo,
    		sum(IF(tco_id=2,obl_monto,0)) as interes_monto,
    		sum(IF(tco_id=2,obl_saldo,0)) as interes_saldo,
    		sum(IF(tco_id=3,obl_monto,0)) as pagado
    		")
    	->where([
    		'estado' => 1,
    	])
    	->whereIn('ppa_id',$planes)
    	->where('obl_monto','>',0)
    	->whereDate('obl_fecha_vencimiento', '<=', Carbon::now() )
    	->when($limite>0,function($query)use($limite,$tipo_corte){
    		if($tipo_corte == 1){
    			return $query->whereDate('obl_fecha_vencimiento','>=',Carbon::now()->subDays($limite));
    		} else {
    			return $query->whereDate('obl_fecha_vencimiento','>=',Carbon::now()->subMonth($limite));
    		}
    	})
    	->groupBy('fecha')
    	->orderBy('obl_fecha_vencimiento','desc')
    	->when($limite>0,function($query)use($limite){
    		return $query->limit($limite);
    	})
    	->get();
    	return response()->json(array_reverse($todo->toArray()),200);
    }

    public function exportar(Request $request){
    	$id_sede = $request->route('id_sede');
		$search = $request->query('search','');
		$id_tipo_pago = $request->query('id_tipo_pago',0);
		$id_departamento = $request->query('id_departamento',0);
		$id_carrera = $request->query('id_carrera',0);
		$fecha_inicio = $request->query('fecha_inicio','');
		$fecha_fin = $request->query('fecha_fin','');

		return (new PagoExport($id_sede,$search,$id_tipo_pago,$id_departamento,$id_carrera,$fecha_inicio,$fecha_fin))->download('pagos.xlsx');
    }

    public function reporte_pago(Request $request){
    	$f = new \NumberFormatter("es", \NumberFormatter::SPELLOUT);
    	$id_sede = $request->route('id_sede');
    	$id_pago = $request->route('id_pago');

    	$pago = Pago::find($id_pago);
    	$id_plan_pago = 0;
    	if(!is_null($pago->inscripcion)){
    		$inscripcion = $pago->inscripcion;
    		if(!is_null($pago->plan_pago)){
    			$id_plan_pago = $pago->plan_pago->id;
    		}
    	} else {
    		$plan_pago = PlanPago::find($pago->id_plan_pago);
    		$id_plan_pago = $plan_pago->id;
    		$inscripcion = Inscripcion::find($plan_pago->id_inscripcion);
    	}

	    $jasper = new JasperPHP;
    	//$input = storage_path("app/reportes/alumno_pago.jasper");
    	$input = storage_path("app/reportes/alumno_pago_raw.jasper");
	    $output = storage_path("app/reportes/".uniqid());
	    $ext = "pdf";

		$entera = floor($pago->monto);      // 1
		$decimal = ($pago->monto - $entera)*100; // .25
		if($decimal>0){
			$monto_nombre = $f->format(intval($entera))." con ".$f->format(intval($decimal))." centavos";
		} else {
			$monto_nombre = $f->format($entera);
		}

		$direccion = $inscripcion->alumno->domicilio." ".
			$inscripcion->alumno->numero." ".
			$inscripcion->alumno->piso." ".
			$inscripcion->alumno->depto;

	    $jasper->process(
	        $input,
	        $output,
	        [$ext],
	        [
	        	'REPORT_LOCALE' => 'es_AR',
	        	'id_inscripcion' => $inscripcion->id,
	        	'id_plan_pago' => $id_plan_pago,
	        	'id_pago' => $id_pago,
	        ],
	        \Config::get('database.connections.mysql')
	    )->execute();
	    
	    $filename ='recibo_pago-nro_'.$pago->numero;
	    return response()->download($output . '.' . $ext, $filename,['Content-Type: application/pdf'])->deleteFileAfterSend();
    }

    public function tipos(Request $request){
    	$todo = TipoPago::where('estado',1)->get();
    	return response()->json($todo,200);
    }
}

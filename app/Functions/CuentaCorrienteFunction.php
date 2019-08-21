<?php

namespace App\Functions;


use App\Models\PlanPago;
use App\Models\PlanPagoPrecio;
use App\Models\Pago;
use App\Models\Obligacion;
use App\Models\ObligacionPago;
use App\Models\ObligacionInteres;

use Carbon\Carbon;

class CuentaCorrienteFunction{

	public static function armar($id_sede,$id_plan_pago,$borrar_pagos = false){
		$plan_pago = PlanPago::find($id_plan_pago);
		$obligaciones = Obligacion::where([
			'estado' => 1,
			'ppa_id' => $id_plan_pago,
		])
		->where('tob_id',1)
		->orderBy('obl_fecha_vencimiento','asc')
		->get();

		if($borrar_pagos){
			
		} else {
			foreach ($obligaciones as $obligacion) {
				CuentaCorrienteFunction::interes_calcular($obligacion->obl_id);
			}
		}

		$obligaciones = Obligacion::where([
			'estado' => 1,
			'ppa_id' => $id_plan_pago,
		])
		->where('tob_id',2)
		->orderBy('obl_fecha_vencimiento','asc')
		->get();
		foreach ($obligaciones as $obligacion) {
			$existe = Obligacion::find($obligacion->id_obligacion);
			if(!$existe->estado){
				$obligacion = Obligacion::find($obligacion->id);
				$obligacion->estado = 0;
				$obligacion->save();
			}
		}
		
		$obligaciones = Obligacion::with([
    	'tipo',
    	'obligacion',
    	'pagos.pago',
    	'interes',
    	'intereses'=>function($q){
    		$q->where('estado',1);
    	}])
    	->where([
			'estado' => 1,
			'ppa_id' => $id_plan_pago,
		])
		->whereIn('tob_id',[1,2])
		->orderByRaw('obl_fecha_vencimiento asc,tob_id asc')
		->get();
		return $obligaciones;
	}

	/**
	*	$id_obligacion del Tipo CUOTA
	*/
	public static function interes_calcular($id_obligacion,$aplicar = true){
		$original = Obligacion::find($id_obligacion);
		$fecha = (new Carbon($original->obl_fecha_vencimiento))->startOfDay();
		$fecha_ahora = Carbon::now()->startOfDay();
		if($fecha <= $fecha_ahora){
			try {
			    \DB::beginTransaction();
			    $obligacion = Obligacion::where('obl_id_obligacion',$id_obligacion)->first();
				if($obligacion){
					$obligacion->obl_descripcion = "Interes ".$original->descripcion;
		    		$obligacion->save();
					ObligacionInteres::where('obl_id',$obligacion->obl_id)->update([
						'estado' => 0,
					]);
				} else {
					$obligacion = new Obligacion;
		    		$obligacion->obl_id_obligacion = $id_obligacion;
		    		$obligacion->tob_id = 2;
		    		$obligacion->obl_fecha= $original->obl_fecha;
		    		$obligacion->obl_fecha_vencimiento = $original->obl_fecha_vencimiento;
		    		$obligacion->ppa_id = $original->ppa_id;
		    		$obligacion->obl_monto = 0;
		    		$obligacion->obl_saldo = 0;
		    		$obligacion->obl_descripcion = "Interes ".$original->descripcion;
		    		$obligacion->save();
				}
				$plan_pago = PlanPago::where([
					'estado' => 1,
					'ppa_id' => $original->ppa_id,
				])->orderBy('created_at','desc')->first();
				$pagos = \DB::table('tbl_obligacion_pago')->selectRaw('opa_monto as monto, pag_fecha as fecha')
					->join('tbl_pagos','tbl_pagos.pag_id','tbl_obligacion_pago.pag_id')
					->where([
						'tbl_obligacion_pago.obl_id' => $id_obligacion,
						'tbl_obligacion_pago.estado' => 1,
					])
					->orderBy('pag_fecha','asc')
					->get();
				$obligacion_saldo = floatval($original->obl_monto);
				$fecha_pago = null;
				foreach ($pagos as $pago) {
					$fecha_pago = new Carbon($pago->fecha);
					if($fecha_pago>$fecha ){
						if($fecha_pago>=$fecha_ahora){
							$fecha_pago = $fecha_ahora;
						}
						CuentaCorrienteFunction::unInteres($plan_pago,$fecha,$fecha_pago,$obligacion_saldo,$obligacion->obl_id);
					}
					$obligacion_saldo = $obligacion_saldo - floatval($pago->monto);
				}
				if($obligacion_saldo>0){
					if(is_null($fecha_pago)){
						if($fecha<$fecha_ahora){
							CuentaCorrienteFunction::unInteres($plan_pago,$fecha,$fecha_ahora,$obligacion_saldo,$obligacion->obl_id);
						}
					} else {
						if($fecha_pago<$fecha_ahora){
							CuentaCorrienteFunction::unInteres($plan_pago,$fecha_pago,$fecha_ahora,$obligacion_saldo,$obligacion->obl_id);
						}
					}
				}
				if($obligacion_saldo<0){
					$obligacion_saldo = 0;
				}
				$original->saldo = $obligacion_saldo;
				$original->save();
				CuentaCorrienteFunction::interes_descripcion($obligacion->obl_id);
				$obligacion = Obligacion::find($obligacion->obl_id);
			    if($aplicar){
			    	\DB::commit();
			    } else {
			    	\DB::rollBack();
			    }
			    return $obligacion;
			} catch (\PDOException $e) {
			    \DB::rollBack();
			}

		}
		return null;
	}

	public static function unInteresHoy($plan_pago,$obligacion_saldo,$id_obligacion){
		$fecha_ahora = Carbon::now();
		$obligacion = Obligacion::find($id_obligacion);
		$fecha = Carbon::parse($obligacion->fecha_vencimiento);
		CuentaCorrienteFunction::unInteres(
			$plan_pago,
			$fecha,
			$fecha_ahora,
			$obligacion_saldo,
			$id_obligacion
		);
	}

	/**
	*	Fecha de inicio del interes
	*	Fecha Pago es el periodo de corte o hasta
	*/
	public static function unInteres($plan_pago,$fecha,$fecha_pago,$obligacion_saldo,$id_obligacion){
		$interes_ultimo = ObligacionInteres::where([
			'estado' => 1,
			'obl_id' => $id_obligacion,
		])
		->whereDate('oin_fecha','<=',$fecha_pago)
		->orderBy('oin_fecha','desc')
		->first();
		if($interes_ultimo){
			$fecha_ultimo = (new Carbon($interes_ultimo->fecha))->startOfMonth();
			$meses = $fecha_ultimo->diffInMonths($fecha_pago);
			$mora = round($meses * $plan_pago->ppa_interes_monto,2);
			if($mora>0 ){
				$interes_ultimo->oin_fecha_hasta = $fecha_pago->toDateString();
				$interes_ultimo->oin_saldo = $obligacion_saldo;
				$interes_ultimo->oin_interes = $plan_pago->ppa_interes_monto;
				$interes_ultimo->oin_monto = $mora;
				$interes_ultimo->oin_cantidad_meses = $meses;
				$interes_ultimo->save();
			}
		} else {
			$fecha_ultimo = $fecha;
			$meses = $fecha_ultimo->startOfMonth()->diffInMonths($fecha_pago);
			$mora = round($meses * $plan_pago->ppa_interes_monto,2);
			if($mora>0 ){
				$todo = new ObligacionInteres;
				$todo->obl_id = $id_obligacion;
				$todo->oin_fecha = $fecha;
				$todo->oin_fecha_hasta = $fecha_pago->toDateString();
				$todo->oin_saldo = $obligacion_saldo;
				$todo->oin_interes = $plan_pago->ppa_interes_monto;
				$todo->oin_monto = $mora;
				$todo->oin_cantidad_meses = $meses;
				$todo->save();
			}
		}
		
		
	}

	/**
	*	$id_obligacion del tipo INTERES
	*/
	public static function interes_descripcion($id_obligacion){
		$obligacion = Obligacion::where([
			'tob_id' => 2,
			'obl_id' => $id_obligacion,
		])->first();
		if($obligacion){
			$intereses = ObligacionInteres::selectRaw('obl_id,sum(oin_monto) as total')
			->where([
				'estado' => 1,
				'obl_id' => $id_obligacion,
			])->groupBy('obl_id')->first();
			if($intereses){
				$intereses = round($intereses->total,2);
			} else {
				$intereses = 0;
			}
			$pagos = ObligacionPago::selectRaw('obl_id,sum(opa_monto) as total')->where([
				'obl_id' => $id_obligacion,
				'estado' => 1,
			])->groupBy('obl_id')->first();
			if($pagos){
				$pagos = round($pagos->total,2);
			} else {
				$pagos = 0;
			}
			$saldo = $intereses - $pagos;
			$obligacion->obl_monto = $intereses;
			$obligacion->obl_saldo = $saldo;
			$obligacion->save();
			return true;
		}
		return false;
	}

	public static function ultimo_precio_plan($id_sede){
		$ultimo = PlanPagoPrecio::where([
			'estado' => 1,
			'sed_id' => $id_sede,
		])->orderBy('created_at','desc')->first();

		if(!$ultimo){
			$ultimo = PlanPagoPrecio::where([
				'estado' => 1,
				'sed_id' => 0,
			])->orderBy('created_at','desc')->first();
		}
		return $ultimo;
	}
}
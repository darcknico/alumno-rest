<?php

namespace App\Functions;


use App\Models\Diaria;
use App\Models\Movimiento;

use Carbon\Carbon;

class DiariaFunction{

	/**
	*	Las diarias se abren y cierran de Lunes a Viernes. Luego el Sabado hasta el Lunes es una sola diaria
	*/
	public static function abrir($id_sede,$fecha){
		$diaria = Diaria::where([
				'sed_id'=>$id_sede,
				'estado'=> 1,
			])
			->whereDate('fecha_inicio','<=',$fecha)
			->whereNull('fecha_fin')
			->orderBy('fecha_inicio','desc')->first();
		if($diaria){
			return $diaria;
		} else {
			$diaria = null;
			$diaria_anterior = Diaria::where([
					'sed_id'=>$id_sede,
					'estado'=> 1,
				])
				->whereDate('fecha_fin','<=',$fecha)
				->whereNotNull('fecha_fin')
				->orderBy('fecha_fin','desc')
				->first();
			/*
			if(
				$fecha->dayOfWeek == Carbon::SATURDAY or 
				$fecha->dayOfWeek == Carbon::SUNDAY or
				$fecha->dayOfWeek == Carbon::MONDAY) {

				if($fecha->dayOfWeek != Carbon::SATURDAY){
				    $fecha = new Carbon('last saturday');
				}
				
			}
			*/
			$fecha = $fecha->startOfDay();
			if($diaria_anterior){
				$diaria = new Diaria;
				$diaria->fecha_inicio = $fecha->toDateTimeString();
				$diaria->saldo_anterior = $diaria_anterior->saldo;
				$diaria->saldo_otros_anterior = $diaria_anterior->saldo_otros_anterior;
				$diaria->id_sede = $id_sede;
				$diaria->save();
			} else {
				$diaria = new Diaria;
				$diaria->fecha_inicio = $fecha->toDateTimeString();
				$diaria->saldo_otros_anterior = 0;
				$diaria->saldo_anterior = 0;
				$diaria->id_sede = $id_sede;
				$diaria->save();
			}
			
		}
		return $diaria;
	}

	
	public static function cerrar($id_sede,$fecha){
		$diaria = Diaria::where([
				'sed_id'=>$id_sede,
				'estado'=> 1,
			])
			->whereDate('fecha_inicio','<=',$fecha->endOfDay())
			->whereNull('fecha_fin')
			->orderBy('created_at','desc')->first();
		if($diaria){
			return DiariaFunction::actualizar_diaria($diaria,$fecha);
		} else {
			return null;
		}
	}
	

	public static function agregar($id_sede,$id_movimiento){
		$movimiento = Movimiento::find($id_movimiento);
		$fecha = Carbon::parse($movimiento->fecha);
		$diaria = Diaria::where([
				'sed_id' => $id_sede,
				'estado' => 1,
			])
			->whereDate('fecha_inicio','<=',$fecha)
			->whereDate('fecha_fin','>=',$fecha)
			->first();
		if(!$diaria){
			$diaria = Diaria::where([
					'sed_id' => $id_sede,
					'estado' => 1,
				])
				->whereDate('fecha_inicio','<=',$fecha)
				->whereNull('fecha_fin')
				->first();
		}
		if($diaria){
			if($movimiento->id_tipo_egreso_ingreso == 1){
				if($movimiento->id_forma_pago == 1){
					$diaria->total_ingreso += $movimiento->monto;
				} else {
					$diaria->total_otros_ingreso += $movimiento->monto;
				}
			} else {
				if($movimiento->id_forma_pago == 1){
					$diaria->total_egreso += $movimiento->monto;
				} else {
					$diaria->total_otros_egreso += $movimiento->monto;
				}
			}
			if($movimiento->id_forma_pago == 1){
				$diaria->saldo = $diaria->saldo_anterior + $diaria->total_ingreso - $diaria->total_egreso;
			} else {
				$diaria->saldo_otros = $diaria->saldo_otros_anterior + $diaria->total_otros_ingreso - $diaria->total_otros_egreso;
			}
			
			$diaria->save();
			DiariaFunction::actualizar($id_sede,Carbon::parse($diaria->fecha_inicio));
		} else {

			/*
			$diaria = DiariaFunction::abrir($id_sede,$fecha);
			$diaria = Diaria::find($diaria->id);
			if($movimiento->id_tipo_egreso_ingreso == 1){
				$diaria->total_ingreso += $movimiento->monto;
			} else {
				$diaria->total_egreso += $movimiento->monto;
			}
			$diaria->saldo = $diaria->saldo_anterior + $diaria->total_ingreso - $diaria->total_egreso;
			$diaria->save();
			$fecha_ahora = Carbon::now();
			if(
				$fecha_ahora->dayOfWeek == Carbon::SATURDAY or 
				$fecha_ahora->dayOfWeek == Carbon::SUNDAY or
				$fecha_ahora->dayOfWeek == Carbon::MONDAY) {
				$fecha_ahora = new Carbon('last saturday');
			}
			if($fecha < $fecha_ahora->startOfDay() ){
				DiariaFunction::cerrar($id_sede,$fecha);
				DiariaFunction::actualizar($id_sede,Carbon::parse($diaria->fecha_inicio));
			}
			*/
		}
		return $diaria;
	}

	public static function quitar($id_sede,$id_movimiento){
		$movimiento = Movimiento::find($id_movimiento);
		$fecha = Carbon::parse($movimiento->fecha);
		$diaria = Diaria::where([
				'sed_id' => $id_sede,
				'estado' => 1,
			])
			->whereDate('fecha_inicio','<=',$fecha)
			->whereDate('fecha_fin','>=',$fecha)
			->first();
		if(!$diaria){
			$diaria = Diaria::whereDate('fecha_inicio','<=',$fecha)->whereNull('fecha_fin')->orderBy('fecha_inicio','desc')->first();
		}
		if($movimiento->id_tipo_egreso_ingreso == 1){
			if($movimiento->id_forma_pago == 1){
				$diaria->total_ingreso -= $movimiento->monto;
			} else {
				$diaria->total_otros_ingreso -= $movimiento->monto;
			}
		} else {
			if($movimiento->id_forma_pago == 1){
				$diaria->total_egreso -= $movimiento->monto;
			} else {
				$diaria->total_otros_egreso -= $movimiento->monto;
			}
		}
		if($movimiento->id_forma_pago == 1){
			$diaria->saldo = $diaria->saldo_anterior + $diaria->total_ingreso - $diaria->total_egreso;
		} else {
			$diaria->saldo_otros = $diaria->saldo_otros_anterior + $diaria->total_otros_ingreso - $diaria->total_otros_egreso;
		}
		$diaria->save();
		DiariaFunction::actualizar($id_sede,Carbon::parse($diaria->fecha_inicio));
		return $diaria;
	}


	public static function actualizar($id_sede,$fecha = null){
		if (is_null($fecha)) {
			$diaria = Diaria::where([
				'sed_id' => $id_sede,
				'estado' => 1,
			])
			->orderBy('fecha_inicio','asc')
			->first();
			if($diaria){
				$fecha = Carbon::parse($diaria->fecha_inicio);
			} else {
				$fecha = Carbon::now();
			}
		}
		$diarias = Diaria::where([
			'sed_id' => $id_sede,
			'estado' => 1,
		])
		->whereDate('fecha_inicio','>=',$fecha)
		->orderBy('fecha_inicio','asc')
		->get();
		$saldo_anterior = 0;
		$saldo_otros_anterior = 0;
		$primero = false;
		foreach ($diarias as $diaria) {
			$diaria = Diaria::find($diaria->id);
			if($primero){
				$diaria->saldo_anterior = $saldo_anterior;
				$diaria->saldo_otros_anterior = $saldo_otros_anterior;
			} else {
				$primero = true;
			}
			$diaria = DiariaFunction::actualizar_diaria($diaria);
			$saldo_anterior = $diaria->saldo;
		}
	}

	public static function actualizar_diaria(Diaria $diaria,$fecha = null){
		$anterior = DiariaFunction::anterior($diaria);
		$saldo_anterior = 0;
		$saldo_otros_anterior = 0;
		if($anterior){
			$saldo_anterior = $anterior->saldo;
			$saldo_otros_anterior = $anterior->saldo_otros;
		}
		$diaria->saldo_anterior = $saldo_anterior;
		$diaria->saldo_otros_anterior = $saldo_otros_anterior;
		
		$movimientos = Movimiento::selectRaw('
			sum(if(tei_id=1 and fpa_id = 1,mov_monto,0)) as total_ingreso,
			sum(if(tei_id=0 and fpa_id = 1,mov_monto,0)) as total_egreso,
			sum(if(tei_id=1 and fpa_id > 1,mov_monto,0)) as total_otros_ingreso,
			sum(if(tei_id=0 and fpa_id > 1,mov_monto,0)) as total_otros_egreso
			')
		->where([
			'estado'=>1,
			'sed_id'=>$diaria->id_sede,
		])
		->whereDate('fecha','>=',$diaria->fecha_inicio)
		->when(is_null($fecha)and $diaria->fecha_fin,function($q)use($diaria){
			$q->whereDate('fecha','<=',$diaria->fecha_fin);
		})
		->when(!is_null($fecha),function($q)use($fecha){
			$q->whereDate('fecha','<=',$fecha->endOfDay());
		})
		->groupBy('sed_id')
		->first();
		if($movimientos){
			$diaria->total_ingreso = $movimientos->total_ingreso;
			$diaria->total_egreso = $movimientos->total_egreso;
			$diaria->total_otros_ingreso = $movimientos->total_otros_ingreso;
			$diaria->total_otros_egreso = $movimientos->total_otros_egreso;
			$diaria->saldo = $saldo_anterior + $movimientos->total_ingreso - $movimientos->total_egreso;
			$diaria->saldo_otros = $saldo_otros_anterior + $movimientos->total_otros_ingreso - $movimientos->total_otros_egreso;
		} else {
			$diaria->total_ingreso = 0;
			$diaria->total_egreso = 0;
			$diaria->total_otros_ingreso = 0;
			$diaria->total_otros_egreso = 0;
			$diaria->saldo = $saldo_anterior;
			$diaria->saldo_otros = $saldo_otros_anterior;
		}
		if($fecha){
			$diaria->fecha_fin = $fecha->endOfDay();
		}
		$diaria->save();
		return $diaria;
	}

	public static function anterior(Diaria $diaria){
		$id_sede = $diaria->id_sede;

        return Diaria::where([
            'estado' => 1,
            'sed_id' => $id_sede,
        ])
        ->whereDate('fecha_inicio','<',$diaria->fecha_inicio)
        ->orderBy('fecha_inicio','desc')
        ->first();
	}

	public static function siguiente(Diaria $diaria){
		$id_sede = $diaria->id_sede;

        return Diaria::where([
            'estado' => 1,
            'sed_id' => $id_sede,
        ])
        ->whereDate('fecha_inicio','>',$diaria->fecha_inicio)
        ->orderBy('fecha_inicio','asc')
        ->first();
	}
}
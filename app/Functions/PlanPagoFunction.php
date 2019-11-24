<?php

namespace App\Functions;


use App\Models\PlanPago;
use App\Models\PlanPagoPrecio;
use App\Models\Pago;
use App\Models\Obligacion;
use App\Models\ObligacionPago;
use App\Models\ObligacionInteres;

use Carbon\Carbon;

class PlanPagoFunction{

	public static function actualizarById($id_plan_pago,$todo = false){
		$plan = PlanPago::find($id_plan_pago);
		if($plan){
			PlanPagoFunction::actualizar($plan,$todo);
		}
	}

	public static function actualizar(PlanPago $plan,$todo = false){
		$obligacion = Obligacion::selectRaw('ppa_id,sum(obl_monto) as total')->where([
	      'ppa_id' => $plan->id,
	      'estado' => 1,
	    ])
	    ->where('tob_id',1)
	    ->groupBy('ppa_id')->first();
	    $plan->cuota_total = $obligacion['total']??0;

	    $obligacion = Obligacion::selectRaw('ppa_id,sum(obl_monto) as total')->where([
	      'ppa_id' => $plan->id,
	      'estado' => 1,
	    ])
	    ->whereIn('tob_id',[3])
	    ->groupBy('ppa_id')->first();
	    $plan->cuota_pagado = $obligacion['total']??0;

    	$obligacion = Obligacion::selectRaw('ppa_id,sum(obl_monto) as total')->where([
	      'ppa_id' => $plan->id,
	      'estado' => 1,
	    ])
	    ->whereIn('tob_id',[11])
	    ->groupBy('ppa_id')->first();

	    $obligacion_matricula = Obligacion::where([
	      'ppa_id' => $plan->id,
	      'tob_id' => 10,
	      'estado' => 1,
	    ])
	    ->where('obl_saldo','>',0)
	    ->first();

	    $plan->matricula_pagado = $obligacion['total']??0;
	    $matricula_saldo = $plan->matricula_monto - $plan->matricula_pagado;
	    if($matricula_saldo<0){
	    	$matricula_saldo = 0;
	    }
    	$plan->matricula_saldo = $matricula_saldo;
    	if($obligacion_matricula){
			$obligacion_matricula->saldo = $matricula_saldo;
    		$obligacion_matricula->save();
    	}
	    $plan->save();

	    if($todo){
	    	$obligaciones = Obligacion::where('id_plan_pago',$plan->id)
	    	->whereIn('id_tipo_obligacion',[1,2])
	    	->where('estado',1)
	    	->get();
	    	foreach ($obligaciones as $obligacion) {
	    		$obligacion = Obligacion::find($obligacion->id);
	    		ObligacionFunction::actualizar($obligacion);
	    	}
	    }
	    return $plan;
	}

	public static function preparar_obligaciones($anio,$matricula_monto,$cuota_monto,$beca_porcentaje,$cuota_cantidad = 10,$dias_vencimiento = 9,$fecha = null){
		if(is_null($fecha)){
        	$fecha = Carbon::createFromDate($anio,2, 1);
		} else {
			$fecha = Carbon::parse($fecha);
		}
        $obligaciones = [];
        $matricula = [
            'descripcion' => 'Matricula Inscripcion',
            'id_tipo_obligacion' => 10,
            'monto' => $matricula_monto,
            'saldo' => $matricula_monto,
            'fecha' => $fecha->toDateString(),
            'fecha_vencimiento' => $fecha->copy()->addDays($dias_vencimiento)->toDateString(),
        ];

        $obligaciones[] = $matricula;
        $total = $matricula_monto;
        if($beca_porcentaje>0){
            $cuota_monto = $cuota_monto - $cuota_monto*($beca_porcentaje/100);
        }
        for ($i=0; $i < $cuota_cantidad ; $i++) {
            $fecha = PlanPagoFunction::siguiente_mes($fecha);
            $obligacion = [
                'descripcion' => 'Cuota '.$fecha->formatLocalized('%B').' '.$fecha->year,
        		'id_tipo_obligacion' => 1,
                'monto' => $cuota_monto,
                'saldo' => $cuota_monto,
                'fecha' => $fecha->toDateString(),
                'fecha_vencimiento' => $fecha->copy()->addDays($dias_vencimiento)->toDateString(),
            ];
            $total += $cuota_monto;
            $obligaciones[] = $obligacion;
        }

        return [
            'total' => $total,
            'obligaciones' => $obligaciones,
        ];
    }

    public static function siguiente_mes(Carbon $fecha){
		return $fecha->copy()->addMonthNoOverflow();
    }
}
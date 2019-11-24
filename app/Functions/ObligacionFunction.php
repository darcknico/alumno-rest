<?php

namespace App\Functions;

use App\Models\Obligacion;
use App\Models\ObligacionPago;

use Carbon\Carbon;

class ObligacionFunction{

	public static function actualizar(Obligacion $obligacion) 
	{
		$pagado = ObligacionFunction::pagado($obligacion);
		$saldo = $obligacion->monto - $pagado;
		if($saldo<0){
			$saldo = 0;
		}
		$obligacion->saldo = $saldo;
		$obligacion->pagado = $pagado;
		$obligacion->save();

		return $obligacion;
	}

	public static function pagado(Obligacion $obligacion){
		$pagado = ObligacionPago::selectRaw('sum(opa_monto) as total')
			->where('obl_id',$obligacion->id)
			->where('estado',1)
			->groupBy('obl_id')
			->first();
		return $pagado->total??0;
	}
}
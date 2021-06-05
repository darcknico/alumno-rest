<?php

namespace App\Functions;

use App\Models\Comision;
use App\Models\ComisionAlumno;
use Carbon\Carbon;

class ComisionFunction{

	public static function actualizar($comision) 
	{
		$alumnos_cantidad = ComisionAlumno::selectRaw('count(*) as total')
            ->where([
                'estado' => 1,
                'com_id' => $comision->id_comision,
            ])->groupBy('com_id')->first();
        $comision->alumnos_cantidad = $alumnos_cantidad->total??0;

        if(is_null($comision->id_aula_virtual) or strlen($comision->id_aula_virtual)==0){
        	$part1 = str_pad($comision->id_carrera, 3, "0", STR_PAD_LEFT);
        	$part2 = str_pad($comision->id_materia, 4, "0", STR_PAD_LEFT);
        	$part3 = str_pad($comision->id, 4, "0", STR_PAD_LEFT);
        	$comision->id_aula_virtual = "{$part1}-{$part2}-{$part3}";
        }
        $comision->save();

        return $comision;
	}

	public static function actualizarById($id_comision){
		return self::actualizar(Comision::find($id_comision));
	}
}
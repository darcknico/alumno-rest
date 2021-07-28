<?php

namespace App\Functions;

use App\Models\Materia;
use Carbon\Carbon;

class MateriaFunction{

	public static function actualizar($materia) 
	{

        $part1 = str_pad($materia->planEstudio->id_carrera, 3, "0", STR_PAD_LEFT);
        $part2 = str_pad($materia->id, 5, "0", STR_PAD_LEFT);
        if(is_null($materia->id_aula_virtual) or strlen($materia->id_aula_virtual)==0){
            $materia->id_aula_virtual = "{$part1}-{$part2}";
        }
        if(is_null($materia->id_examen_virtual) or strlen($materia->id_examen_virtual)==0){
            $materia->id_examen_virtual = "{$part1}-{$part2}";
        }
        $materia->save();

        return $materia;
	}

	public static function actualizarById($id_materia){
		return self::actualizar(Materia::find($id_materia));
	}
}
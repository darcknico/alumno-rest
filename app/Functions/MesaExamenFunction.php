<?php

namespace App\Functions;

use App\Models\Mesa\MesaExamen;
use App\Models\Mesa\MesaExamenMateria;
use App\Models\Mesa\MesaExamenMateriaAlumno;

use Carbon\Carbon;

class MesaExamenFunction{

	public static function actualizar(MesaExamen $mesa_examen, $todo = false){
		if($todo){
			$materias = MesaExamenMateria::where('id_mesa_examen',$mesa_examen->id)
			->where('estado',1)
			->get();
			foreach ($materias as $materia) {
				$materia = MesaExamenMateria::find($materia->id);
				MesaExamenFunction::actualizar_materia($materia);
			}
		}
	}

	public static function actualizar_materia(MesaExamenMateria $materia){
		$alumnos_cantidad_presente = MesaExamenMateriaAlumno::selectRaw('count(*) as total')
            ->where([
                'estado' => 1,
                'mma_id' => $materia->id,
                'mam_asistencia' => 1,
            ])->groupBy('mma_id')->first();
        $materia->alumnos_cantidad_presente = $alumnos_cantidad_presente->total??0;

        $alumnos_cantidad = MesaExamenMateriaAlumno::selectRaw('count(*) as total, SUM(IF(mam_nota_final<6,1,0)) as no_aprobado, SUM(IF(mam_nota_final>5,1,0)) as aprobado')
            ->where([
                'estado' => 1,
                'mma_id' => $materia->id,
            ])
            ->groupBy('estado')->first();
        $materia->alumnos_cantidad = $alumnos_cantidad->total??0;
        $materia->alumnos_cantidad_aprobado = $alumnos_cantidad->aprobado??0;
        $materia->alumnos_cantidad_no_aprobado = $alumnos_cantidad->no_aprobado??0;
        $materia->save();

	}
}
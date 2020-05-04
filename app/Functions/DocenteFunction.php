<?php

namespace App\Functions;

use App\Models\Inscripcion;
use App\Models\Academico\Docente;
use App\Models\Academico\DocenteEstado;
use App\Models\Academico\DocenteMateria;
use App\Models\Comision;
use App\Models\ComisionAlumno;
use App\Models\PlanEstudio;
use App\Models\Materia;
use App\Models\Mesa\MesaExamenMateria;
use App\Models\Mesa\MesaExamenMateriaAlumno;
use App\Models\Mesa\AlumnoMateriaNota; //notas viejo

use Carbon\Carbon;

class DocenteFunction{

	public static function actualizar(Docente $docente){
        $docente->id_tipo_docente_estado = self::getEstadoAt($docente);
        $docente->save();
	}

    public static function getEstadoAt(Docente $docente,Carbon $fecha = null){
        if(is_null($fecha)){
            $fecha = Carbon::now();
        }
        //INACTIVO
        if($docente->id_tipo_docente_estado == 1){
            return 1;
        }
        //JUBILACION
        $jubilado = DocenteEstado::where('id_usuario',$docente->id_usuario)
            ->where('id_tipo_docente_estado',3)
            ->whereDate('fecha_inicial','<=',$fecha->toDateString())
            ->first();
        if($jubilado){
            return 3;
        }
        //LICENCIA
        $licencia = DocenteEstado::where('id_usuario',$docente->id_usuario)
            ->where('id_tipo_docente_estado',4)
            ->whereDate('fecha_inicial','<=',$fecha->toDateString())
            ->whereDate('fecha_final','>=',$fecha->toDateString())
            ->first();
        if($licencia){
            return 4;
        }
        //ACTIVO
        return 2;
    }


}
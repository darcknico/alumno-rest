<?php

namespace App\Functions;

use App\Models\Inscripcion;
use App\Models\Comision;
use App\Models\ComisionAlumno;
use App\Models\PlanEstudio;
use App\Models\Materia;
use App\Models\Mesa\MesaExamenMateria;
use App\Models\Mesa\MesaExamenMateriaAlumno;
use App\Models\Mesa\AlumnoMateriaNota; //notas viejo

use Carbon\Carbon;

class InscripcionFunction{

	public static function actualizar(Inscripcion $inscripcion){

        $inscripcion->id_periodo_lectivo = self::obtenerIdPeriodoLectivo($inscripcion);
        $inscripcion->porcentaje_aprobados = self::obtenerPorcentajeAprobado($inscripcion);
        $todos = self::obtenerTPFinal($inscripcion);
        $aprobados = self::obtenerTPFinalAprobados($inscripcion);
        $inscripcion->final_total = $todos['total'];
        $inscripcion->final_total_aprobados = $aprobados['total'];
        $inscripcion->final_promedio = $todos['promedio'];
        $inscripcion->final_promedio_aprobados = $aprobados['promedio'];
        $inscripcion->save();
	}

    public static function obtenerIdPeriodoLectivo(Inscripcion $inscripcion){
        $anio = Carbon::now()->year;
        $periodo = Materia::where('id_plan_estudio',$inscripcion->id_plan_estudio)
            ->whereHas('comisiones',function($q)use($inscripcion,$anio){
                $q->whereHas('alumnos',function($qt)use($inscripcion){
                    $qt->where('estado',1)->where('id_inscripcion',$inscripcion->id);
                })
                ->where('estado',1)
                ->where('anio','<',$anio);
            })
            ->orderBy('id_tipo_materia_lectivo','desc')
            ->first();
        $tipo_lectivo = 1;
        if($periodo){
            $tipo_lectivo = $periodo->tipoLectivo->id;
            if($tipo_lectivo<4){
                $tipo_lectivo++;
            }
        }
        $periodo = Materia::where('id_plan_estudio',$inscripcion->id_plan_estudio)
            ->whereHas('comisiones',function($q)use($inscripcion,$anio){
                $q->whereHas('alumnos',function($qt)use($inscripcion){
                    $qt->where('estado',1)->where('id_inscripcion',$inscripcion->id);
                })
                ->where('estado',1)
                ->where('anio','=',$anio);
            })
            ->orderBy('id_tipo_materia_lectivo','desc')
            ->first();
        if($periodo){
            if($tipo_lectivo<$periodo->tipoLectivo->id){
                $tipo_lectivo = $periodo->tipoLectivo->id;
            }
        }
        return $tipo_lectivo;
    }

    public static function obtenerPorcentajeAprobado(Inscripcion $inscripcion){
        $total = Materia::where('estado',1)
            ->where('id_plan_estudio',$inscripcion->id_plan_estudio)
            ->count();
        if($total == 0){
            return 0;
        }
        $aprobados = MesaExamenMateria::where('estado',1)
        ->whereHas('alumnos',function($q)use($inscripcion){
            $q->where('estado',1)
            ->where('id_inscripcion',$inscripcion->id)
            ->whereNotNull('nota_final')
            ->where('nota_final','>=',4);
        })
        ->distinct('id_materia')
        ->get();

        $viejo = AlumnoMateriaNota::where('estado',1)
        ->where('id_inscripcion',$inscripcion->id)
        ->where('nota','>=',4)
        ->whereNotIn('id_materia',$aprobados->pluck('id_materia'))
        ->distinct('id_materia')
        ->count();

        $aprobados = count($aprobados) + $viejo;
        return $aprobados * 100 / $total;
    }

    public static function obtenerTPFinal(Inscripcion $inscripcion){
        $result1 = MesaExamenMateriaAlumno::selectRaw('ins_id, count(ins_id) as total, avg(mam_nota_final) as promedio')
            ->whereNotNull('nota_final')
            ->where('estado',1)
            ->where('id_inscripcion',$inscripcion->id)
            ->groupBy('ins_id')
            ->first();

        $result2 = AlumnoMateriaNota::selectRaw('ins_id, count(ins_id) as total, avg(amn_nota) as promedio')
            ->where('estado',1)
            ->where('id_inscripcion',$inscripcion->id)
            ->groupBy('ins_id')
            ->first();
        $total1 = $result1->total??0;
        $total2 = $result2->total??0;
        $prom1 = $result1->promedio??0;
        $prom2 = $result2->promedio??0;
        $total = $total1+$total2;
        if($total == 0){
            return [
                'total' => 0,
                'promedio' => 0,
            ];
        } else {
            return [
                'total' => $total,
                'promedio' => ($total1*$prom1 + $total2*$prom2)/$total,
            ];
        }
        
    }
    public static function obtenerTPFinalAprobados(Inscripcion $inscripcion){
        $result = MesaExamenMateriaAlumno::selectRaw('ins_id, count(ins_id) as total, avg(mam_nota_final) as promedio')
            ->whereNotNull('nota_final')
            ->where('estado',1)
            ->where('id_inscripcion',$inscripcion->id)
            ->where('nota','>=',4)
            ->groupBy('ins_id')
            ->first();
        $result2 = AlumnoMateriaNota::selectRaw('ins_id, count(ins_id) as total, avg(amn_nota) as promedio')
            ->where('estado',1)
            ->where('id_inscripcion',$inscripcion->id)
            ->where('nota','>=',4)
            ->groupBy('ins_id')
            ->first();
        $total1 = $result1->total??0;
        $total2 = $result2->total??0;
        $prom1 = $result1->promedio??0;
        $prom2 = $result2->promedio??0;
        $total = $total1+$total2;
        if($total == 0){
            return [
                'total' => 0,
                'promedio' => 0,
            ];
        } else {
            return [
                'total' => $total,
                'promedio' => ($total1*$prom1 + $total2*$prom2)/$total,
            ];
        }
    }

}
<?php

namespace App\Filters;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

use App\Functions\AuxiliarFunction;

class PlanPagoFilter{

	/**
     * Filtro de seleccion de actas.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
	public static function index(Request $request,Builder $query){
        $search = $request->query('search','');
        $id_departamento = $request->query('id_departamento',0);
        $id_carrera = $request->query('id_carrera',0);
        $id_tipo_materia_lectivo = $request->query('id_tipo_materia_lectivo',0);
        $anio = $request->query('anio',0);
        $deudores = $request->query('deudores',0); //0 TODOS - 1 SI - 2 NO

        return PlanPagoFilter::query(
            $search,
            $id_departamento,
            $id_carrera,
            $id_tipo_materia_lectivo,
            $anio,
            $deudores,
            $query
        );
	}

    public static function query($search,$id_departamento,$id_carrera,$id_tipo_materia_lectivo,$anio,$deudores,Builder $query){
        $query = $query
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
            return $q->whereIn('ins_id',$inscripciones);
          })
          ->when($id_carrera>0,function($q)use($id_carrera){
            $q->whereHas('inscripcion',function($qt)use($id_carrera){
                $qt->where([
                    'car_id' => $id_carrera,
                    'estado' => 1,
                ]);
              });
          })
          ->when($deudores>0,function($q)use($deudores){
            if($deudores>1){
              return $q->whereNotIn('ppa_id',function($qt){
                return $qt->select('ppa_id')->from('tbl_obligaciones')->where([
                  'estado' => 1,
                ])->where('obl_saldo','>',0);
              });
            } else {
              return $q->whereIn('ppa_id',function($qt){
                return $qt->select('ppa_id')->from('tbl_obligaciones')->where([
                  'estado' => 1,
                ])->where('obl_saldo','>',0);
              });
            }
          })
          ->when($anio>0,function($q)use($anio){
            $q->where('anio',$anio);
          })
          ->when($id_tipo_materia_lectivo>0,function($s)use($id_tipo_materia_lectivo){
            $s->whereHas('inscripcion',function($q)use($id_tipo_materia_lectivo){
                $q->where([
                    'estado' => 1,
                ])->whereHas('comisiones',function($qt)use($id_tipo_materia_lectivo){
                    $qt->where('estado',1)
                        ->whereHas('comision',function($qtr)use($id_tipo_materia_lectivo){
                            $qtr->where('estado',1)
                                ->whereHas('materia',function($qtrs)use($id_tipo_materia_lectivo){
                                    $qtrs->where('estado',1)->where('id_tipo_materia_lectivo',$id_tipo_materia_lectivo);
                                });
                        });
                });
            });
          });
        $values = explode(" ", $search);
        if(count($values)>0){
          foreach ($values as $key => $value) {
            if(strlen($value)>0){
              $query = $query->where(function($query) use  ($value) {
                $query->where('ppa_matricula_monto',$value)
                  ->orWhere('ppa_cuota_monto',$value)
                  ->orWhereHas('inscripcion',function($q)use($value){
                    $q->whereIn('alu_id',function($qt)use($value){
                        $qt->select('alu_id')->from('tbl_alumnos')
                        ->where('estado',1)
                        ->where(function($qtz) use  ($value){
                            $qtz->where('alu_nombre','like','%'.$value.'%')
                            ->orWhere('alu_apellido','like','%'.$value.'%')
                            ->orWhere('alu_documento',$value);
                        });
                      });
                  });
              });
            }
          }
        }
        return $query;
    }
}
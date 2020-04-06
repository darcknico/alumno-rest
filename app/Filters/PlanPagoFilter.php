<?php

namespace App\Filters;

use App\Models\Carrera;
use App\Models\Inscripcion;

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
          $id_beca = $request->query('id_beca',0);
          $id_tipo_materia_lectivo = $request->query('id_tipo_materia_lectivo',0);
          $anio = $request->query('anio',0);
          $deudores = $request->query('deudores',0); //0 TODOS - 1 SI - 2 NO
          $id_tipo_inscripcion_estado = $request->query('id_tipo_inscripcion_estado',null); //0 TODOS - 1 SI - 2 NO
          $sin_cobranzas = $request->query('sin_cobranzas',false);
          $fecha_inicial = $request->query('fecha_inicial',"");
          $fecha_final = $request->query('fecha_final',"");

          return PlanPagoFilter::fill([
              'search' => $search,
              'id_departamento' => $id_departamento,
              'id_carrera' => $id_carrera,
              'id_beca' => $id_beca,
              'id_tipo_materia_lectivo' => $id_tipo_materia_lectivo,
              'anio' => $anio,
              'deudores' => $deudores,
              'id_tipo_inscripcion_estado' => $id_tipo_inscripcion_estado,
              'sin_cobranzas' => $sin_cobranzas,
              'fecha_inicial' => $fecha_inicial,
              'fecha_final' => $fecha_final,
            ],
            $query
          );
  	}

    public static function fill($filters,Builder $query){
        $search = $filters['search']??"";
        $id_departamento = $filters['id_departamento']??0;
        $id_carrera = $filters['id_carrera']??0;
        $id_beca = $filters['id_beca']??0;
        $id_tipo_materia_lectivo = $filters['id_tipo_materia_lectivo']??0;
        $anio = $filters['anio']??0;
        $deudores = $filters['deudores']??0;
        $id_tipo_inscripcion_estado = $filters['id_tipo_inscripcion_estado']??null;
        $sin_cobranzas = $filters['sin_cobranzas']??false;
        $fecha_inicial = $filters['fecha_inicial']??"";
        $fecha_final = $filters['fecha_final']??"";

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
          ->when($id_beca>0,function($q)use($id_beca){
            $q->whereHas('inscripcion',function($qt)use($id_beca){
                $qt->where([
                    'bec_id' => $id_beca,
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
          })
          ->when( !is_null($id_tipo_inscripcion_estado) ,function($q)use($id_tipo_inscripcion_estado){
            if(is_numeric($id_tipo_inscripcion_estado) and $id_tipo_inscripcion_estado>0){
                $q->whereHas('inscripcion',function($qt)use($id_tipo_inscripcion_estado){
                  $qt->where('id_tipo_inscripcion_estado',$id_tipo_inscripcion_estado);
                });
            } else if($id_tipo_inscripcion_estado!=0) {
                $q->whereHas('inscripcion',function($qt)use($id_tipo_inscripcion_estado){
                  $tipos = explode(',', $id_tipo_inscripcion_estado);
                  if(count($tipos)>0){
                      return $qt->whereIn('id_tipo_inscripcion_estado', array_map('intval',$tipos) );
                  }
                });
            }
          })
          ->when($sin_cobranzas,function($q)use($fecha_inicial,$fecha_final){
            if(!empty($fecha_inicial) and !empty($fecha_final)){
              $q->whereDoesntHave('pagos',function($qt)use($fecha_inicial,$fecha_final){
                $qt
                  ->where('estado',1)
                  ->whereDate('fecha','>=',$fecha_inicial)
                  ->whereDate('fecha','<=',$fecha_final);
              });
            }
          })
          ->when(!$sin_cobranzas,function($q)use($fecha_inicial,$fecha_final){
            $q->when(strlen($fecha_inicial)>0,function($qt)use($fecha_inicial){
                $qt->whereDate('created_at','>=',$fecha_inicial);
            })
            ->when(strlen($fecha_final)>0,function($qt)use($fecha_final){
                $qt->whereDate('created_at','<=',$fecha_final);
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
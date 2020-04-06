<?php

namespace App\Filters;

use App\Models\Carrera;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

use App\Functions\AuxiliarFunction;

class InscripcionFilter{

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
          $id_tipo_inscripcion_estado = $request->query('id_tipo_inscripcion_estado',0);
          $anio_inicial = $request->query('anio_inicial',0);
          $anio_final = $request->query('anio_final',0);
          $fecha_inicial = $request->query('fecha_inicial',"");
          $fecha_final = $request->query('fecha_final',"");

          return InscripcionFilter::fill([
              'search' => $search,
              'id_departamento' => $id_departamento,
              'id_carrera' => $id_carrera,
              'id_beca' => $id_beca,
              'id_tipo_inscripcion_estado' => $id_tipo_inscripcion_estado,
              'anio_inicial' => $anio_inicial,
              'anio_final' => $anio_final,
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
        $id_tipo_inscripcion_estado = $filters['id_tipo_inscripcion_estado']??0;
        $anio_inicial = $filters['anio_inicial']??0;
        $anio_final = $filters['anio_final']??0;
        $fecha_inicial = $filters['fecha_inicial']??"";
        $fecha_final = $filters['fecha_final']??"";

        $query = $query
            ->when($id_departamento>0,function($q)use($id_departamento){
                $carreras = Carrera::where([
                    'dep_id' => $id_departamento,
                    'estado' => 1,
                ])->pluck('car_id')->toArray();
                return $q->whereIn('car_id',$carreras);
            })
            ->when($id_carrera>0,function($q)use($id_carrera){
                return $q->where('car_id',$id_carrera);
            })
            ->when($id_beca>0,function($q)use($id_beca){
                return $q->where('bec_id',$id_beca);
            })
            ->when($id_tipo_inscripcion_estado>0,function($q)use($id_tipo_inscripcion_estado){
                return $q->where('tie_id',$id_tipo_inscripcion_estado);
            })
            ->when($anio_inicial>0,function($q)use($anio_inicial){
                return $q->where('anio','>=',$anio_inicial);
            })
            ->when($anio_final>0,function($q)use($anio_final){
                return $q->where('anio','<=',$anio_final);
            })
            ->when(strlen($fecha_inicial)>0,function($q)use($fecha_inicial){
                return $q->whereDate('created_at','>=',$fecha_inicial);
            })
            ->when(strlen($fecha_final)>0,function($q)use($fecha_final){
                return $q->whereDate('created_at','<=',$fecha_final);
            });
        $values = explode(" ", $search);
        if(count($values)>0){
          foreach ($values as $key => $value) {
            if(strlen($value)>0){
              $query = $query->where(function($query) use  ($value) {
                $query->whereHas('alumno',function($q)use($value){
                    $q->where('alu_nombre','like','%'.$value.'%')
                    ->orWhere('alu_apellido','like','%'.$value.'%')
                    ->orWhere('alu_documento',$value);
                  });
              });
            }
          }
        }
        return $query;
    }
}
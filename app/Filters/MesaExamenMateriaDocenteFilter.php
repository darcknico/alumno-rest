<?php

namespace App\Filters;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

use App\Functions\AuxiliarFunction;

class MesaExamenMateriaDocenteFilter{

  	/**
       * Filtro de seleccion de actas.
       *
       * @return \Illuminate\Database\Eloquent\Builder
       */
  	public static function index(Request $request,Builder $query){
          $search = $request->query('search','');
          $id_departamento = $request->query('id_departamento',0);
          $id_carrera = $request->query('id_carrera',0);
          $id_materia = $request->query('id_materia',0);
          $id_mesa_examen = $request->query('id_mesa_examen',0);
          $id_usuario = $request->query('id_usuario',0);
          $fecha_inicial = $request->query('fecha_inicial',"");
          $fecha_final = $request->query('fecha_final',"");

          return MesaExamenMateriaDocenteFilter::fill([
              'search' => $search,
              'id_departamento' => $id_departamento,
              'id_carrera' => $id_carrera,
              'id_materia' => $id_materia,
              'id_mesa_examen' => $id_mesa_examen,
              'id_usuario' => $id_usuario,
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
        $id_materia = $filters['id_materia']??0;
        $id_mesa_examen = $filters['id_mesa_examen']??0;
        $id_usuario = $filters['id_usuario']??0;
        $fecha_inicial = $filters['fecha_inicial']??"";
        $fecha_final = $filters['fecha_final']??"";

        $query = $query
            ->when($id_departamento>0,function($q)use($id_departamento){
                $carreras = Carrera::where([
                    'dep_id' => $id_departamento,
                    'estado' => 1,
                ])->pluck('car_id')->toArray();
                return $q->whereHas('mesa_examen_materia',function($qt)use($carreras){
                    $qt->whereIn('car_id',$carreras);
                });
            })
            ->when($id_carrera>0,function($q)use($id_carrera){
                return $q->whereHas('mesa_examen_materia',function($qt)use($id_carrera){
                    $qt->where('car_id',$id_carrera);
                });
            })
            ->when($id_materia>0,function($q)use($id_materia){
                return $q->whereHas('mesa_examen_materia',function($qt)use($id_materia){
                    $qt->where('mat_id',$id_carrera);
                });
            })
            ->when($id_mesa_examen>0,function($q)use($id_mesa_examen){
                return $q->where('id_mesa_examen',$id_mesa_examen);
            })
            ->when($id_usuario>0,function($q)use($id_usuario){
                $q->where('id_usuario',$id_usuario);
            })
            ->when(strlen($fecha_inicial),function($q)use($fecha_inicial){
                return $q->whereHas('mesa_examen_materia',function($qt)use($fecha_inicial){
                    $qt->whereDate('fecha','>=',$fecha_inicial);
                });
            })
            ->when(strlen($fecha_final),function($q)use($fecha_final){
                return $q->whereHas('mesa_examen_materia',function($qt)use($fecha_final){
                    $qt->whereDate('fecha','<=',$fecha_final);
                });
            });
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
                if(strlen($value)>0){
                    $query = $query->where(function($query) use  ($value) {
                        $query->whereHas('usuario',function($q)use($value){
                            $q->where('nombre','like','%'.$value.'%')
                            ->orWhere('apellido','like','%'.$value.'%')
                            ->orWhere('documento','like','%'.$value.'%');
                        });
                    });
                }
            }
        }
        return $query;
    }
}
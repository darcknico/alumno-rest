<?php

namespace App\Filters;

use App\Models\Carrera;
use App\Models\UsuarioSede;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

use App\Functions\AuxiliarFunction;

class ComisionFilter{

  	/**
       * Filtro de seleccion de actas.
       *
       * @return \Illuminate\Database\Eloquent\Builder
       */
  	public static function index(Request $request,Builder $query){
          return self::fill(self::extract($request),
            $query
          );
  	}

    public static function extract(Request $request){
        $search = $request->query('search','');

        $id_departamento = $request->query('id_departamento',0);
        $id_carrera = $request->query('id_carrera',0);
        $id_materia = $request->query('id_materia',0);
        $anio = $request->query('anio',null);
        $cerrado = $request->query('cerrado',null);
        $id_usuario = $request->query('id_usuario',0);
        $id_inscripcion = $request->query('id_inscripcion',0);

        return [
          'search' => $search,
          'id_departamento' => $id_departamento,
          'id_carrera' => $id_carrera,
          'id_materia' => $id_materia,
          'anio' => $anio,
          'cerrado' => $cerrado,
          'id_usuario' => $id_usuario,
          'id_inscripcion' => $id_inscripcion,
        ];
    }

    public static function fill($filters,Builder $query){
        $user = Auth::user();
        $search = $filters['search']??"";

        $id_departamento = $filters['id_departamento'];
        $id_carrera = $filters['id_carrera'];
        $id_materia = $filters['id_materia'];
        $anio = $filters['anio'];
        $cerrado = $filters['cerrado'];
        $id_usuario = $filters['id_usuario'];
        $id_inscripcion = $filters['id_inscripcion'];
        
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
            ->when($id_materia>0,function($q)use($id_materia){
                return $q->where('mat_id',$id_materia);
            })
            ->when(!empty($anio),function($q)use($anio){
                return $q->where('anio',$anio);
            })
            ->when(!empty($cerrado),function($q)use($cerrado){
                return $q->where('cerrado',$cerrado);
            })
            ->when($id_inscripcion>0,function($q)use($id_inscripcion){
                $q->whereHas('alumnos',function($qt)use($id_inscripcion){
                    $qt->where('estado',1)
                        ->where('id_inscripcion',$id_inscripcion);
                });
            })
            ->when($user->id_tipo_usuario == 8,function($q)use($user){
                $q->whereHas('docentes',function($qt)use($user){
                    $qt->where('id_usuario',$user->id)->where('estado',1);
                });
            })
            ->when($id_usuario>0,function($q)use($id_usuario){
                $q->whereHas('docentes',function($qt)use($id_usuario){
                    $qt->where('id_usuario',$id_usuario)->where('estado',1);
                });
            });
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
                if(strlen($value)>0){
                    $query = $query->where(function($query) use  ($value) {
                        $query->where('anio', $value)
                            ->orWhere('numero','like','%'.$value.'%')
                            ->orWhereIn('car_id',function($q)use($value){
                                return $q->select('car_id')->from('tbl_carreras')->where([
                                    'estado' => 1,
                                ])->where(function($qt) use ($value) {
                                    $qt->where('car_nombre','like','%'.$value.'%')->orWhere('car_nombre_corto','like','%'.$value.'%');
                                });
                            })
                            ->orWhereIn('mat_id',function($q)use($value){
                                return $q->select('mat_id')->from('tbl_materias')->where([
                                    'estado' => 1,
                                ])->where(function($qt) use ($value) {
                                    $qt->where('mat_nombre','like','%'.$value.'%')->orWhere('mat_codigo','like','%'.$value.'%');
                                });
                            });
                    });
                }
            }
        }

        return $query;
    }
}
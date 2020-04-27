<?php

namespace App\Filters;

use App\Models\Carrera;
use App\Models\UsuarioSede;
use App\Models\Academico\DocenteMateria;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

use App\Functions\AuxiliarFunction;

class DocenteMateriaFilter{

  	/**
       * Filtro de seleccion de actas.
       *
       * @return \Illuminate\Database\Eloquent\Builder
       */
  	public static function index(Request $request,Builder $query){
          $search = $request->query('search','');
          $id_sede = $request->query('id_sede',0);
          $id_usuario = $request->query('id_usuario',0);
          $id_materia = $request->query('id_materia',0);
          $id_carrera = $request->query('id_carrera',0);
          $id_departamento = $request->query('id_departamento',0);
          $id_tipo_docente_cargo = $request->query('id_tipo_docente_cargo',0);

          return self::fill([
              'search' => $search,
              'id_sede' => $id_sede,
              'id_usuario' => $id_usuario,
              'id_materia' => $id_materia,
              'id_carrera' => $id_carrera,
              'id_departamento' => $id_departamento,
              'id_tipo_docente_cargo' => $id_tipo_docente_cargo,
            ],
            $query
          );
  	}

    public static function fill($filters,Builder $query){
        $search = $filters['search']??"";
        $id_sede = $filters['id_sede']??0;
        $id_usuario = $filters['id_usuario']??0;
        $id_materia = $filters['id_materia']??0;
        $id_carrera = $filters['id_carrera']??0;
        $id_departamento = $filters['id_departamento']??0;
        $id_tipo_docente_cargo = $filters['id_tipo_docente_cargo']??0;

        $query
            ->when($id_sede>0,function($q)use($id_sede){
                return $q->where('id_sede',$id_sede);
            })
            ->when($id_usuario>0,function($q)use($id_usuario){
                return $q->where('id_usuario',$id_usuario);
            })
            ->when($id_materia>0,function($q)use($id_materia){
                return $q->where('id_materia',$id_materia);
            })
            ->when($id_carrera>0,function($q)use($id_carrera){
                return $q->where('id_carrera',$id_carrera);
            })
            ->when($id_departamento>0,function($q)use($id_departamento){
                return $q->whereHas('carrera',function($qt)use($id_departamento){
                    $qt->where('id_departamento',$id_departamento);
                });
            })
            ->when($id_tipo_docente_cargo>0,function($q)use($id_tipo_docente_cargo){
                return $q->where('id_tipo_docente_cargo',$id_tipo_docente_cargo);
            });
        
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
              if(strlen($value)>0){
                $registros = $registros->where(function($query) use  ($value) {
                  $query->whereHas('usuario',function($q)use($value){
                    $q->where('apellido','like','%'.$value.'%')
                        ->orWhere('nombre','like','%'.$value.'%')
                        ->orWhere('documento','like','%'.$value.'%');
                  })
                  ->orWhereHas('materia',function($q)use($value){
                    $q->where('codigo','like','%'.$value.'%')
                        ->orWhere('nombre','like','%'.$value.'%');
                  });
                });
              }
            }
        }
        return $query;
    }
}
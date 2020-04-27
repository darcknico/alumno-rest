<?php

namespace App\Filters;

use App\Models\Carrera;
use App\Models\UsuarioSede;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

use App\Functions\AuxiliarFunction;

class DocenteFilter{

  	/**
       * Filtro de seleccion de actas.
       *
       * @return \Illuminate\Database\Eloquent\Builder
       */
  	public static function index(Request $request,Builder $query){
          $search = $request->query('search','');
          $estado = $request->query('estado',true);
          $id_sede = $request->query('id_sede',0);
          $id_tipo_contrato = $request->query('id_tipo_contrato',0);
          $id_carrera = $request->query('id_carrera',0);

          return self::fill([
              'search' => $search,
              'estado' => $estado,
              'id_sede' => $id_sede,
              'id_tipo_contrato' => $id_tipo_contrato,
              'id_carrera' => $id_carrera,
            ],
            $query
          );
  	}

    public static function fill($filters,Builder $query){
        $search = $filters['search']??"";
        $estado = $filters['estado'];
        $id_sede = $filters['id_sede'];
        $id_tipo_contrato = $filters['id_tipo_contrato'];
        $id_carrera = $filters['id_carrera'];

        $query
            ->when($id_sede>0,function($q)use($id_sede){
              $q->whereHas('sedes',function($qt)use($id_sede){
                $qt->where('id_sede',$id_sede)->where('estado',1);
              });
            })
            ->when( !is_null($estado) and is_bool($estado),function($q)use($estado){
                return $q->whereHas('usuario',function($qt)use($estado){
                    $qt->where('estado',($estado?1:0));
                });
            })
            ->when($id_tipo_contrato>0,function($q)use($id_tipo_contrato){
                $q->whereHas('contratos',function($qt)use($id_tipo_contrato){
                    return $q->where('id_tipo_contrato',$id_tipo_contrato);
                });
            })
            ->when($id_carrera>0,function($q)use($id_carrera){
                return $q->whereHas('carreras',function($qt)use($id_carrera){
                    $qt->where('estado',1)->where('id_carrera',$id_carrera);
                });
            });
        
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
              if(strlen($value)>0){
                $query->where(function($query) use  ($value) {
                  $query->whereHas('usuario',function($q)use($value){
                    $q->where('apellido','like','%'.$value.'%')
                        ->orWhere('nombre','like','%'.$value.'%')
                        ->orWhere('documento','like','%'.$value.'%');
                    })
                    ->orWhere('cuit','like','%'.$value.'%')
                    ->orWhere('titulo','like','%'.$value.'%');
                });
              }
            }
        }
        return $query;
    }
}
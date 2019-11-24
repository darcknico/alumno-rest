<?php

namespace App\Filters;

use App\Models\Carrera;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

use App\Functions\AuxiliarFunction;

class MesaExamenMateriaFilter{

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
        $fecha_ini = $request->query('fecha_ini',null);
        $fecha_fin = $request->query('fecha_fin',null);
        $cierre = $request->query('cierre',null);
        $id_usuario = $request->query('id_usuario',0);

        return MesaExamenMateriaFilter::fill([
                'search' => $search,
                'id_departamento' => $id_departamento,
                'id_carrera' => $id_carrera,
                'id_materia' => $id_materia,
                'id_mesa_examen' => $id_mesa_examen,
                'fecha_ini' => $fecha_ini,
                'fecha_fin' => $fecha_fin,
                'cierre' => $cierre,
                'id_usuario' => $id_usuario,
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
        $fecha_ini = $filters['fecha_ini']??null;
        $fecha_fin = $filters['fecha_fin']??null;
        $cierre = $filters['cierre']??null;
        $id_usuario = $filters['id_usuario']??null;


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
            ->when($id_mesa_examen>0,function($q)use($id_mesa_examen){
                return $q->where('id_mesa_examen',$id_mesa_examen);
            })
            ->when($fecha_ini>0,function($q)use($fecha_ini){
                return $q->whereDate('fecha','>=',$fecha_ini);
            })
            ->when($fecha_fin>0,function($q)use($fecha_fin){
                return $q->whereDate('fecha','<=',$fecha_fin);
            })
            ->when(!is_null($cierre),function($q)use($cierre){
                $cierre = AuxiliarFunction::is_true($cierre);
                if($cierre){
                    return $q->whereNotNull('fecha_cierre');
                } else {
                    return $q->whereNull('fecha_cierre');
                }
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
                        $query->whereIn('car_id',function($q)use($value){
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
                            })
                            ->orWhere('libro','like','%'.$value.'%')
                            ->orWhere('folio_libre','like','%'.$value.'%')
                            ->orWhere('folio_promocion','like','%'.$value.'%')
                            ->orWhere('folio_regular','like','%'.$value.'%');
                    });
                }
            }
        }

        return $query;
    }
}
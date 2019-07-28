<?php

namespace App\Http\Controllers\Extra;

use App\Models\Alumno;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SqlAudit as Audit;

/**
 * 
 */
class AuditoriaController extends Controller
{
    public static function cast_json($registros){
        $resultado = [];
        foreach ($registros as $registro) {
            $objecto = new \stdClass();
            foreach ($registro->new_values as $key => $value) {
                $new_key = substr($key, 4);
                $objecto->$new_key = $value;
            }
            $registro->new_values = $objecto;
            $objecto = new \stdClass();
            foreach ($registro->old_values as $key => $value) {
                $new_key = substr($key, 4);
                $objecto->$new_key = $value;
            }
            $registro->old_values = $objecto;

            $resultado[] = $registro;
        }
        return $resultado;
    }
	
    public function alumnos(Request $request){
        $id_sede = $request->route('id_sede');
        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);

        $alumnos = Alumno::whereHas('sedes',function($q)use($id_sede){
            return $q->where('sed_id',$id_sede)->where('estado',1);
        })->get()->pluck('id')->toArray();
        $registros = Audit::with('user','auditable')
        	->where('auditable_type',Alumno::class)->whereIn('auditable_id',$alumnos);
        
        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros->orderBy('created_at','desc')
            ->get();
            return response()->json(AuditoriaController::cast_json($todo),200);
        }

        if(strlen($sort)>0){
        $registros = $registros->orderBy($sort,$order);
        } else {
        $registros = $registros->orderBy('created_at','desc');
        }
        $sql = $registros->toSql();
        $q = clone($registros->getQuery());
        $total_count = count($q->get());
        if($length>0){
        $registros = $registros->limit($length);
        if($start>1){
            $registros = $registros->offset($start)->get();
        } else {
            $registros = $registros->get();
        }

        } else {
            $registros = $registros->get();
        }

        return response()->json([
            'total_count'=>intval($total_count),
            'items'=> AuditoriaController::cast_json($registros),
        ],200);
    }

}
<?php

namespace App\Http\Controllers\Academico;

use App\Models\Inscripcion;
use App\Models\Academico\InscripcionEstado;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Validator;
use Carbon\Carbon;

class InscripcionEstadoController extends Controller
{
    public function index(Request $request)
    {

        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);
        $registros = InscripcionEstado::query();
        
        $id_inscripcion = $request->query('id_inscripcion',0);
        $id_sede = $request->query('id_sede');
        $id_tipo_inscripcion_estado = $request->query('id_tipo_inscripcion_estado',0);
        $id_alumno = $request->query('id_alumno',0);

        $registros = $registros
            ->when($id_sede>0,function($q)use($id_sede){
                $q->whereHas('inscripcion',function($qt)use($id_sede){
                  $qt->where('id_sede','=',$id_sede);
                });
            })
            ->when($id_inscripcion>0,function($q)use($id_inscripcion){
                $q->where('id_inscripcion',$id_inscripcion);
            })
            ->when($id_tipo_inscripcion_estado>0,function($q)use($id_tipo_inscripcion_estado){
                $q->where('id_tipo_inscripcion_estado',$id_tipo_inscripcion_estado);
            })
            ->when($id_alumno>0,function($q)use($id_alumno){
                $q->whereHas('inscripcion',function($qt)use($id_alumno){
                    $qt->where('id_alumno','=',$id_alumno);
                });
            });
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
              if(strlen($value)>0){

              }
            }
        }
        if( strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros->orderBy('created_at','desc')
            ->get();
            return response()->json($todo,200);
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
            'items'=>$registros,
        ],200);
    }
}

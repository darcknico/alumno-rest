<?php

namespace App\Http\Controllers\App;

use App\Models\AlumnoDispositivo;
use App\Models\AlumnoNotificacion;
use App\Models\Notificacion;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NotificacionNueva;

use Carbon\Carbon;

class AlumnoDispositivoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);
        $id_usuario = $request->query('id_usuario',0);
        
        $registros = AlumnoDispositivo::with('alumno')->where([
            'estado' => 1,
        ]);

        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $length==0 ){
            $todo = $registros->orderBy('created_at','desc')
            ->get();
            return response()->json($todo);
        }
        /*
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
                if(strlen($value)>0){
                    $registros = $registros->where(function($query) use  ($value) {
                        $query->where('titulo','like','%'.$value.'%');
                        });
                    });
                }
            }
        }
        */

        $sql = $registros->toSql();
        $q = clone($registros->getQuery());
        $total_count = $q->groupBy('estado')->count();

        if(strlen($sort)>0){
            $registros = $registros->orderBy($sort,$order);
        } else {
            $registros = $registros->orderBy('created_at','desc');
        }
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
        ]);
    }

    public function notificar(Request $request){
        $id_usuario = Auth::id();
        $id_alumno_dispositivo = $request->input('id_alumno_dispositivo');
        $id_sede = $request->input('id_sede');
        $titulo = $request->input('titulo');
        $cuerpo = $request->input('cuerpo');

        $dispositivo = AlumnoDispositivo::find($id_alumno_dispositivo);

        $notificacion = new Notificacion;
        $notificacion->nombre = $titulo;
        $notificacion->descripcion = $cuerpo;
        $notificacion->fecha = Carbon::now()->toDateTimeString();
        $notificacion->enviado = true;
        $notificacion->puede_email = false;
        $notificacion->id_usuario = $id_usuario;
        $notificacion->save();

        $alumno = new AlumnoNotificacion;
        $alumno->id_alumno = $dispositivo->id_alumno;
        $alumno->id_notificacion = $notificacion->id;
        $alumno->enviado = true;
        $alumno->id_usuario = $id_usuario;
        $alumno->save();

        $dispositivo->notify(new NotificacionNueva($notificacion));

        return response()->json($dispositivo);
    }
}

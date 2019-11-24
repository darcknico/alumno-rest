<?php

namespace App\Http\Controllers\App;

use App\Models\App\Asistencia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);

        $id_usuario = $request->query('id_usuario',0);
        $id_sede = $request->query('id_sede',0);
        
        $registros = Asistencia::with('usuario')->where([
            'estado' => 1,
        ])
        ->when($id_usuario>0,function($q)use($id_usuario){
            $q->where('id_usuario',$id_usuario);
        })
        ->when($id_sede>0,function($q)use($id_sede){
            $q->where('id_sede',$id_sede);
        });

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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'id_usuario_dispositivo' => 'required',
            'fecha' => 'required | date',
        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()],403);
        }

        $id_usuario_dispositivo = $request->input('id_usuario_dispositivo');
        $fecha = $request->input('fecha');
        $latitud = $request->input('latitud');
        $longitud = $request->input('longitud');
        $id_sede = $request->input('id_sede');

        $asistencia = new Asistencia;
        $asistencia->id_usuario = $user->id;
        $asistencia->id_usuario_dispositivo = $id_usuario_dispositivo;
        $asistencia->latitud = $latitud;
        $asistencia->longitud = $longitud;
        $asistencia->id_sede = $id_sede;
        $asistencia->save();
        return response()->json($asistencia);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\App\Asistencia  $asistencia
     * @return \Illuminate\Http\Response
     */
    public function show(Asistencia $asistencia)
    {
        //
        return response()->json($asistencia);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\App\Asistencia  $asistencia
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Asistencia $asistencia)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\App\Asistencia  $asistencia
     * @return \Illuminate\Http\Response
     */
    public function destroy(Asistencia $asistencia)
    {
        //
        $asistencia->estado=0;
        $asistencia->save();
        return response()->json($asistencia);
    }
}

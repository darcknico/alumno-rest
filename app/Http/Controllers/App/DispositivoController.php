<?php

namespace App\Http\Controllers\App;

use App\Models\App\Dispositivo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;

class DispositivoController extends Controller
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
        
        $registros = Dispositivo::with('usuario')->where([
            'estado' => 1,
        ])
        ->when($id_usuario>0,function($q)use($id_usuario){
            $q->where('id_usuario',$id_usuario);
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
            'device_id' => 'required',
        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()],403);
        }

        $device_id = $request->input('device_id');
        $device_model = $request->input('device_model');
        $device_os = $request->input('device_os');
        $manufacturer = $request->input('manufacturer');

        $dispositivo = Dispositivo::where('estado',1)
        ->where('id_usuario',$id_usuario)
        ->where('device_id',$device_id)
        ->first();
        if($dispositivo){
            return response()->json($dispositivo);
        }
        $dispositivo = new Dispositivo;
        $dispositivo->id_usuario = $user->id;
        $dispositivo->device_id = $device_id;
        $dispositivo->device_model = $device_model;
        $dispositivo->device_os = $device_os;
        $dispositivo->manufacturer = $manufacturer;
        $dispositivo->save();
        return response()->json($dispositivo);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\App\Dispositivo  $dispositivo
     * @return \Illuminate\Http\Response
     */
    public function show(Dispositivo $dispositivo)
    {
        $user = Auth::user();
        return response()->json($dispositivo);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\App\Dispositivo  $dispositivo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Dispositivo $dispositivo)
    {
        $user = Auth::user();

        $device_model = $request->input('device_model');
        $device_os = $request->input('device_os');
        $manufacturer = $request->input('manufacturer');

        $dispositivo->device_model = $device_model;
        $dispositivo->device_os = $device_os;
        $dispositivo->manufacturer = $manufacturer;
        $dispositivo->save();
        return response()->json($dispositivo);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\App\Dispositivo  $dispositivo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Dispositivo $dispositivo)
    {
        $dispositivo->estado = 0;
        $dispositivo->save();
        return response()->json($dispositivo);
    }
}

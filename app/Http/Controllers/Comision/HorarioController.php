<?php

namespace App\Http\Controllers\Comision;

use App\Models\Comision\Horario;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;

class HorarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $id_sede = $request->route('id_sede');
        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);
        
        $registros = Horario::with('comision.materia')
            ->whereHas('comision',function($q)use($id_sede){
                $q->where([
                    'estado' => 1,
                    'sed_id' => $id_sede,
                ]);
            })
            ->where([
            'estado' => 1,
        ]);

        $id_departamento = $request->query('id_departamento',0);
        $id_carrera = $request->query('id_carrera',0);
        $id_materia = $request->query('id_materia',0);
        $id_comision = $request->query('id_comision',0);
        $id_dia = $request->query('id_dia',0);
        $anio = $request->query('anio',null);

        $registros = $registros
            ->when($id_departamento>0,function($q)use($id_departamento){
                $carreras = Carrera::where([
                    'dep_id' => $id_departamento,
                    'estado' => 1,
                ])->pluck('car_id')->toArray();
                return $q->whereHas('comision',function($qt)use($carreras){
                    $qt->whereIn('car_id',$carreras);
                });
            })
            ->when($id_carrera>0,function($q)use($id_carrera){
                return $q->whereHas('comision',function($qt)use($id_carrera){
                    $qt->where('car_id',$id_carrera);
                });
            })
            ->when($id_materia>0,function($q)use($id_materia){
                return $q->whereHas('comision',function($qt)use($id_materia){
                    $qt->where('mat_id',$id_carrera);
                });
            })
            ->when(!empty($anio) and $anio>0,function($q)use($anio){
                return $q->whereHas('comision',function($qt)use($anio){
                    $qt->where('com_anio',$anio);
                });
            })
            ->when($id_comision>0,function($q)use($id_comision){
                return $q->where('id_comision',$id_comision);
            })
            ->when($id_dia>0,function($q)use($id_dia){
                return $q->where('id_dia',$id_dia);
            });

        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros
            ->orderBy('id_dia','asc')
            ->orderBy('hora_inicial','asc')
            ->get();
            return response()->json($todo,200);
        }

        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
                if(strlen($value)>0){
                    $registros = $registros->where(function($query) use  ($value) {
                        
                    });
                }
            }
        }

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
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id_sede = $request->route('id_sede');
        $validator = Validator::make($request->all(),[
            'id_dia' => 'required',
            'id_comision' => 'required',
            'hora_inicial' => 'required',
            'hora_final' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $id_dia = $request->input('id_dia');
        $id_comision = $request->input('id_comision');
        $hora_inicial = $request->input('hora_inicial');
        $hora_final = $request->input('hora_final');

        $horarios = Horario::where([
            'estado' => 1,
            'dia_id' => $id_dia,
            'com_id' => $id_comision
        ])->get();
        if(count($horarios)>0){
            $hora_inicial = Carbon::now()->setTimeFromTimeString($hora_inicial);
            $hora_final = Carbon::now()->setTimeFromTimeString($hora_final);
            $insertar = true;
            foreach ($horarios as $horario) {
                $hora_inicial_aux = Carbon::now()->setTimeFromTimeString($horario->hora_inicial);
                $hora_final_aux = Carbon::now()->setTimeFromTimeString($horario->hora_final);
                if($hora_final->lte($hora_inicial_aux)){

                } else if($hora_final->isBefore($hora_final_aux)){
                   $insertar = false;
                   break;
                } else {
                    if($hora_inicial->isBefore($hora_inicial_aux)){
                        $insertar = false;
                        break;
                    } else if($hora_inicial->isBefore($hora_final_aux)){
                        $insertar = false;
                        break;
                    }
                }
            }

            if(!$insertar){
                return response()->json(['error'=>"El horario ingresado esta solapando otro horario."],403);
            }
        }

        $horario = new Horario;
        $horario->id_dia = $id_dia;
        $horario->id_comision = $id_comision;
        $horario->hora_inicial = $hora_inicial;
        $horario->hora_final = $hora_final;
        $horario->save();

        return response()->json($horario,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comision\Horario  $horario
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $horario = Horario::find($request->comisionHorario);
        return response()->json($horario,200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comision\Horario  $horario
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'hora_inicial' => 'required',
            'hora_final' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $id_dia = $request->input('id_dia');
        $hora_inicial = $request->input('hora_inicial');
        $hora_final = $request->input('hora_final');

        $horario = Horario::find($request->comisionHorario);
        $horario->hora_inicial = $hora_inicial;
        $horario->hora_final = $hora_final;
        $horario->save();
        return response()->json($horario,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comision\Horario  $horario
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $horario = Horario::find($request->comisionHorario);
        $horario->estado = 0;
        $horario->save();
        return response()->json($horario,200);
    }
}

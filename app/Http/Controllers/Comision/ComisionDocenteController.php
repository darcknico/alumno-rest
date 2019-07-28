<?php

namespace App\Http\Controllers\Comision;

use App\Models\Comision\Docente;
use App\Models\Academico\Docente as DocenteAcademico;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class ComisionDocenteController extends Controller
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

        $id_departamento = $request->query('id_departamento',0);
        $id_carrera = $request->query('id_carrera',0);
        $id_materia = $request->query('id_materia',0);
        $id_comision = $request->query('id_comision',0);
        $id_usuario = $request->query('id_usuario',0);
        $anio = $request->query('anio',null);

        $registros = Docente::with('comision.materia','docente')
            ->whereHas('comision',function($q)use($id_sede){
                $q->where([
                    'estado' => 1,
                    'sed_id' => $id_sede,
                ]);
            })
            ->where([
            'estado' => 1,
        ]);
            
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
            ->when($id_usuario>0,function($q)use($id_usuario){
                return $q->where('id_usuario',$id_usuario);
            });

        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros->orderBy('created_at','desc')
            ->get();
            return response()->json($todo,200);
        }

        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
                if(strlen($value)>0){
                    $registros = $registros->where(function($query) use  ($value) {
                        $query->whereHas('usuario',function($q)use($value){
                            $q->where('nombre','like','%'.$value.'%')
                            ->orWhere('apellido','like','%'.$value.'%')
                            ->orWhere('documento','like','%'.$value.'%');
                        });
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
            'id_usuario' => 'required',
            'id_comision' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $id_comision = $request->input('id_comision');
        $id_usuario = $request->input('id_usuario');
        $observaciones = $request->input('observaciones',null);

        $comision = Comision::find($id_comision);
        if(!$comision){
            return response()->json(['error'=>'La comision no fue encontrada.'],403);
        }
        $docente = DocenteAcademico::find($id_usuario);
        if(!$docente){
            return response()->json(['error'=>'El docente no fue encontrado.'],403);
        }

        $todo = Docente::where([
            'estado' => 1,
            'usu_id' => $id_alumno,
            'com_id' => $id_comision,
        ])->first();
        if($todo){
            return response()->json(['error'=>'El docente ya fue asociado a la comision.'],403);
        } else {
            $todo = new Docente;
            $todo->id_usuario = $id_comision;
            $todo->id_alumno = $id_usuario;
            $todo->save();
        }

        return response()->json($todo,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comision\Docente  $docente
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $todo = Docente::find($request->comisionDocente);
        return response()->json($todo,200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comision\Docente  $docente
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[

        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $observaciones = $request->input('observaciones');

        $docente = Docente::find($request->comisionDocente);
        $docente->observaciones = $observaciones;
        $docente->save();
        return response()->json($docente,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comision\Docente  $docente
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $docente = Docente::find($request->comisionDocente);

        $docente->estado = 0;
        $docente->save();

        return response()->json($docente,200);
    }
}

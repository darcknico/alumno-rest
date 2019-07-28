<?php

namespace App\Http\Controllers\Mesa;

use App\Models\Carrera;
use App\Models\Academico\Docente;
use App\Models\Mesa\MesaExamenMateria;
use App\Models\Mesa\MesaExamenMateriaDocente;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;

class MesaExamenMateriaDocenteController extends Controller
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
        $id_mesa_examen = $request->query('id_mesa_examen',0);
        $id_usuario = $request->query('id_usuario',0);

        $registros = MesaExamenMateriaDocente::with('mesa_examen_materia.materia','docente')
            ->whereHas('mesa_examen_materia',function($q)use($id_sede){
                $q->whereHas('mesa_examen',function($qt)use($id_sede){
                    $qt->where([
                        'estado' => 1,
                        'sed_id' => $id_sede,
                    ]);
                })
                ->where('estado',1);
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
                return $q->whereHas('mesa_examen_materia',function($qt)use($carreras){
                    $qt->whereIn('car_id',$carreras);
                });
            })
            ->when($id_carrera>0,function($q)use($id_carrera){
                return $q->whereHas('mesa_examen_materia',function($qt)use($id_carrera){
                    $qt->where('car_id',$id_carrera);
                });
            })
            ->when($id_materia>0,function($q)use($id_materia){
                return $q->whereHas('mesa_examen_materia',function($qt)use($id_materia){
                    $qt->where('mat_id',$id_carrera);
                });
            })
            ->when($id_mesa_examen>0,function($q)use($id_mesa_examen){
                return $q->where('id_mesa_examen',$id_mesa_examen);
            })
            ->when($id_usuario>0,function($q)use($id_usuario){
                return $q->whereHas('docentes',function($qt)use($id_usuario){
                    $qt->where('id_usuario',$id_usuario);
                });
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
        $user = Auth::user();

        $validator = Validator::make($request->all(),[
            'id_mesa_examen_materia' => 'required',
            'id_usuario' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $id_mesa_examen_materia = $request->input('id_mesa_examen_materia');
        $id_usuario = $request->input('id_usuario');
        $id_tipo_mesa_docente = $request->input('id_tipo_mesa_docente');
        $observaciones = $request->input('observaciones');

        $materia = MesaExamenMateria::find($id_mesa_examen_materia);
        if(!$materia){
            return response()->json(['error'=>'La mesa de examen no fue encontrada.'],403);
        }

        $docente = Docente::find($id_usuario);
        if(!$docente){
            return response()->json(['error'=>'El docente no existe.'],403);
        }

        $todo = MesaExamenMateriaDocente::where([
            'estado' => 1,
            'usu_id' => $id_usuario,
        ])->where('id_mesa_examen_materia',$id_mesa_examen_materia)->first();
        if($todo){
            return response()->json(['error'=>'El docente ya fue asociado a la mesa de examen.'],403);
        } else {
            $todo = new MesaExamenMateriaDocente;
            $todo->id_mesa_examen_materia = $id_mesa_examen_materia;
            $todo->id_usuario = $id_usuario;
            $todo->id_tipo_mesa_docente = $id_tipo_mesa_docente;
            $todo->observaciones = $observaciones;
            $todo->save();
        }
        
        return response()->json($todo,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mesa\MesaExamenMateriaDocente  $mesaExamenMateriaDocente
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mesa\MesaExamenMateriaDocente  $mesaExamenMateriaDocente
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $id_mesa_examen_materia_docente = $request->route('id_mesa_examen_materia_docente');

        $validator = Validator::make($request->all(),[
            'id_tipo_mesa_docente' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $id_tipo_mesa_docente = $request->input('id_tipo_mesa_docente');
        $observaciones = $request->input('observaciones');

        $todo = MesaExamenMateriaDocente::find($id_mesa_examen_materia_docente);
        $todo->id_tipo_mesa_docente = $id_tipo_mesa_docente;
        $todo->observaciones = $observaciones;
        $todo->save();

        return response()->json($todo,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mesa\MesaExamenMateriaDocente  $mesaExamenMateriaDocente
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id_mesa_examen_materia_docente = $request->route('id_mesa_examen_materia_docente');
        $todo = MesaExamenMateriaDocente::find($id_mesa_examen_materia_docente);
        $todo->estado = 0;
        $todo->save();

        return response()->json($todo,200);
    }
}

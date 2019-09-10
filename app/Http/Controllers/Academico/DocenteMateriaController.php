<?php

namespace App\Http\Controllers\Academico;

use App\Models\Materia;
use App\Models\Academico\DocenteMateria;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Validator;

class DocenteMateriaController extends Controller
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
        $registros = DocenteMateria::with('docente','sede')
            ->where('estado',1);

        $id_sede = $request->query('id_sede',0);
        $id_usuario = $request->query('id_usuario',0);
        $id_materia = $request->query('id_materia',0);
        $id_carrera = $request->query('id_carrera',0);
        $id_departamento = $request->query('id_departamento',0);

        $registros = $registros
            ->when($id_sede>0,function($q)use($id_sede){
                return $q->where('id_sede',$id_sede);
            })
            ->when($id_usuario>0,function($q)use($id_usuario){
                return $q->where('id_usuario',$id_usuario);
            })
            ->when($id_materia>0,function($q)use($id_materia){
                return $q->where('id_materia',$id_materia);
            })
            ->when($id_carrera>0,function($q)use($id_carrera){
                return $q->where('id_carrera',$id_carrera);
            })
            ->when($id_departamento>0,function($q)use($id_departamento){
                return $q->whereHas('carrera',function($qt)use($id_departamento){
                    $qt->where('id_departamento',$id_departamento);
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
                    $q->where('apellido','like','%'.$value.'%')
                        ->orWhere('nombre','like','%'.$value.'%')
                        ->orWhere('documento','like','%'.$value.'%');
                  })
                  ->orWhereHas('materia',function($q)use($value){
                    $q->where('codigo','like','%'.$value.'%')
                        ->orWhere('nombre','like','%'.$value.'%');
                  });
                });
              }
            }
        }
        if(strlen($sort)>0){
        $registros = $registros->orderBy($sort,$order);
        } else {
        $registros = $registros->orderBy('created_at','desc');
        }
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
          'id_sede' => 'required | integer',
          'id_usuario' => 'required | integer',
          'id_materia' => 'required | integer',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $id_sede = $request->input('id_sede');
        $id_usuario = $request->input('id_usuario');
        $id_materia = $request->input('id_materia');

        $materia = Materia::find($id_materia);
        if(!$materia){
            return response()->json(['error'=>'La materia no existe.'],403);
        }

        $encontro = DocenteMateria::where([
            'sed_id' => $id_sede,
            'usu_id' => $id_usuario,
            'mat_id' => $id_materia,
            'estado' => 1,
        ])->first();
        if($encontro){
            return response()->json(['error'=>'El docente ya se encuentra asociado a la materia.'],403);
        }

        $todo = new DocenteMateria;
        $todo->id_sede = $id_sede;
        $todo->id_usuario = $id_usuario;
        $todo->id_materia = $id_materia;
        $todo->id_carrera = $materia->planEstudio->id_carrera;
        $todo->save();

        return response()->json($todo,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Academico\DocenteMateria  $docenteMateria
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $todo = DocenteMateria::find($request->docenteMateria);
        return $todo;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Academico\DocenteMateria  $docenteMateria
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $todo = DocenteMateria::find($request->docenteMateria);
        return $todo ;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Academico\DocenteMateria  $docenteMateria
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $todo = DocenteMateria::find($request->docenteMateria);
        $todo->estado = 0;
        $todo->save();
        return $todo;
    }
}

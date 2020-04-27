<?php

namespace App\Http\Controllers\Academico;

use App\Models\Materia;
use App\Models\Academico\DocenteMateria;
use App\Filters\DocenteMateriaFilter;
use App\Exports\DocenteAsignacionExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;

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

        $registros = DocenteMateriaFilter::index($request,$registros);


        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros->orderBy('created_at','desc')
            ->get();
            return response()->json($todo,200);
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
          'fecha_asignacion' => 'nullable | date',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $id_sede = $request->input('id_sede');
        $id_usuario = $request->input('id_usuario');
        $id_materia = $request->input('id_materia');
        $id_tipo_docente_cargo = $request->input('id_tipo_docente_cargo',null);
        $fecha_asignacion = $request->input('fecha_asignacion',null);
        $horas_catedra = $request->input('horas_catedra',null);

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
        $todo->id_tipo_docente_cargo = $id_tipo_docente_cargo;
        $todo->fecha_asignacion = $fecha_asignacion;
        $todo->horas_catedra = $horas_catedra;
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
        $validator = Validator::make($request->all(),[
          'fecha_asignacion' => 'nullable | date',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $todo = DocenteMateria::find($request->docenteMateria);

        $id_tipo_docente_cargo = $request->input('id_tipo_docente_cargo',null);
        $fecha_asignacion = $request->input('fecha_asignacion',null);
        $horas_catedra = $request->input('horas_catedra',null);

        $todo->id_tipo_docente_cargo = $id_tipo_docente_cargo;
        $todo->fecha_asignacion = $fecha_asignacion;
        $todo->horas_catedra = $horas_catedra;
        $todo->save();
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

    public function exportar(Request $request){

        $fecha = Carbon::now()->format('d.m.Y');

        return (new DocenteAsignacionExport(
            $request->all()
        ))->download('asignaciones'.$fecha.'.xlsx');
    }
}

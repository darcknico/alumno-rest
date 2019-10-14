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
use Carbon\Carbon;
use JasperPHP\JasperPHP;

use App\Filters\MesaExamenMateriaDocenteFilter;

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
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);
        $search = $request->query('search',"");

        $registros = MesaExamenMateriaDocente::with('mesa_examen_materia.materia','mesa_examen_materia.mesa_examen','docente')
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

        $registros = MesaExamenMateriaDocenteFilter::index($request,$registros);
        
        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros->orderBy('created_at','desc')
            ->get();
            return response()->json($todo,200);
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


    public function reporte_docente_mesa(Request $request){
        $id_sede = $request->route('id_sede');
        $user = Auth::user();

        $validator = Validator::make($request->all(),[
            'id_usuario' => 'required | integer',
            'fecha_inicial' => 'required | date',
            'fecha_final' => 'required | date',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $id_usuario = $request->query('id_usuario');
        $fecha_inicial = Carbon::parse($request->query('fecha_inicial'));
        $fecha_final = Carbon::parse($request->query('fecha_final'));
        $docente = Docente::find($id_usuario);

        $diff = $fecha_inicial->diffInMonths($fecha_final);
        $periodo = "";
        if($diff>0){
            $periodo = $fecha_inicial->formatLocalized('%B')." / ".$fecha_final->formatLocalized('%B')." ".$fecha_inicial->year;

        } else {
            $periodo = $fecha_inicial->formatLocalized('%B')." ".$fecha_inicial->year;
        }

        $jasper = new JasperPHP;
        $input = storage_path("app/reportes/alumno_docente_mesa.jasper");
        $output = storage_path("app/reportes/".uniqid());
        $ext = "pdf";

        $jasper->process(
            $input,
            $output,
            [$ext],
            [
                'REPORT_LOCALE' => 'es_AR',
                'id_usuario' => $id_usuario,
                'fecha_inicial' => $fecha_inicial->toDateString(),
                'fecha_final' => $fecha_final->toDateString(),
                'periodo' => $periodo,
                'id_sede' => $id_sede,
                'logo' => storage_path("app/images/logo_2.png"),
            ],
            \Config::get('database.connections.mysql')
        )->execute();
        
        $filename = $docente->usuario->apellido.' '.$docente->usuario->nombre.$ext;
        return response()->download($output . '.' . $ext, $filename)->deleteFileAfterSend();
    }
}

<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\PlanEstudio;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use JasperPHP\JasperPHP; 

class PlanEstudioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $id_carrera = $request->route('id_carrera',0);
        $todo = PlanEstudio::where([
                'estado' => 1 ,
            ])
            ->when($id_carrera>0,function($q)use($id_carrera){
                $q->where('id_carrera',$id_carrera);
            })
            ->orderBy('anio','desc')
            ->get();
        return response()->json($todo,200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id_carrera = $request->route('id_carrera');
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'nombre' => 'required',
            'codigo' => 'required',
            'anio' => 'required | numeric',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $nombre = $request->input('nombre');
        $codigo = $request->input('codigo');
        $anio = $request->input('anio');
        $horas = $request->input('horas',0);
        $resolucion = $request->input('resolucion');

        $todo = new PlanEstudio;
        $todo->nombre = $nombre;
        $todo->codigo = $codigo;
        $todo->anio = $anio;
        $todo->horas = $horas;
        $todo->resolucion = $resolucion;
        $todo->id_carrera = $id_carrera;
        $todo->id_usuario = $user->id;
        $todo->save();

        return response()->json($todo,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id_plan_estudio = $request->route('id_plan_estudio');
        $todo = PlanEstudio::with('usuario','carrera','materias')->find($id_plan_estudio);
        return response()->json($todo,200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function edit(TipoUsuario $tipoUsuario)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $id_plan_estudio = $request->route('id_plan_estudio');
        $validator = Validator::make($request->all(),[
            'nombre' => 'required',
            'codigo' => 'required',
            'anio' => 'required | numeric',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $nombre = $request->input('nombre');
        $codigo = $request->input('codigo');
        $anio = $request->input('anio');
        $horas = $request->input('horas',0);
        $resolucion = $request->input('resolucion');

        $todo = PlanEstudio::find($id_plan_estudio);
        if($todo){
            $todo->nombre = $nombre;
            $todo->nombre = $nombre;
            $todo->anio = $anio;
            $todo->horas = $horas;
            $todo->resolucion = $resolucion;
            $todo->save();
        } 
        return response()->json($todo,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $id_plan_estudio = $request->route('id_plan_estudio');

        $todo = PlanEstudio::find($id_plan_estudio);
        if($todo){
            $todo->estado = 0;
            $todo->save();
        }
        return response()->json($todo,200);
    }

    public function reporte(Request $request){
        $id_sede = $request->route('id_sede');
        $id_plan_estudio = $request->route('id_plan_estudio');
        $plan_estudio = PlanEstudio::find($id_plan_estudio);

        $jasper = new JasperPHP;
        $input = storage_path("app/reportes/alumno_plan_estudio.jasper");
        $output = storage_path("app/reportes/".uniqid());
        $ext = "pdf";

        $jasper->process(
            $input,
            $output,
            [$ext],
            [
                'id_plan_estudio' => $id_plan_estudio,
                'logo'=> storage_path("app/images/logo_constancia.png")??null,
                'REPORT_LOCALE' => 'es_AR',
            ],
            \Config::get('database.connections.mysql')
        )->execute();
        
        $filename ='plan_estudio-'.$plan_estudio->codigo;
        return response()->download($output . '.' . $ext, $filename,['Content-Type: application/pdf'])->deleteFileAfterSend();
    }
}

<?php

namespace App\Http\Controllers\Mesa;

use App\User;
use App\Models\Mesa\MesaExamen;
use App\Models\Mesa\MesaExamenMateria;
use App\Models\Mesa\MesaExamenMateriaAlumno;
use App\Models\Mesa\TipoCondicionAlumno;
use App\Models\Mesa\AlumnoMateriaNota;
use App\Models\Carrera;
use App\Models\CarreraModalidad;
use App\Models\Materia;
use App\Models\Inscripcion;
use App\Models\Alumno;
use App\Models\Comision;
use App\Models\ComisionAlumno;
use App\Models\Asistencia;
use App\Models\Sede;

use App\Events\RegistracionAlumnoMateriaNota;
use App\Exports\AlumnoMateriaNotaExampleExport;
use App\Imports\AlumnoMateriaNotaImport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use Carbon\Carbon;
use JasperPHP\JasperPHP; 
use Maatwebsite\Excel\Facades\Excel;

class AlumnoMateriaNotaController extends Controller
{
    public function index(Request $request){
        $id_inscripcion = $request->route('id_inscripcion');

        $registros = AlumnoMateriaNota::with('materia','condicion')
        ->where([
            'estado' => 1,
            'ins_id' => $id_inscripcion
        ])->orderBy('fecha','desc')->get();

        return response()->json($registros,200);
    }

    public function store(Request $request){
        $id_inscripcion = $request->route('id_inscripcion');
        $user = Auth::user();

        $validator = Validator::make($request->all(),[
            'id_materia' => 'required',
            'asistencia' => 'required',
            'nota' => 'required',
            'nota_nombre' => 'required',
            'id_tipo_condicion_alumno' => 'required',
            'fecha' => 'required | date',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $inscripcion = Inscripcion::find($id_inscripcion);

        $id_materia = $request->input('id_materia');
        $asistencia = $request->input('asistencia');
        $nota = $request->input('nota');
        $nota_nombre = $request->input('nota_nombre');
        $id_tipo_condicion_alumno = $request->input('id_tipo_condicion_alumno');
        $fecha = Carbon::parse($request->input('fecha'));
        $libro = $request->input('libro');
        $folio = $request->input('folio');
        $observaciones = $request->input('observaciones');

        $alumno = new AlumnoMateriaNota;
        $alumno->id_alumno = $inscripcion->id_alumno;
        $alumno->id_inscripcion = $id_inscripcion;
        $alumno->id_materia = $id_materia;
        $alumno->asistencia = $asistencia;
        $alumno->nota = $nota;
        $alumno->nota_nombre = $nota_nombre;
        $alumno->id_tipo_condicion_alumno = $id_tipo_condicion_alumno;
        $alumno->fecha = $fecha;
        $alumno->libro = $libro;
        $alumno->folio = $folio;
        $alumno->observaciones = $observaciones;
        $alumno->usu_id = $user->id;
        $alumno->save();

        event(new RegistracionAlumnoMateriaNota($alumno));
        return response()->json($alumno,200);
    }

    public function update(Request $request){
        $id_alumno_materia_nota = $request->route('id_alumno_materia_nota');

        $validator = Validator::make($request->all(),[
            'asistencia' => 'required',
            'nota' => 'required',
            'nota_nombre' => 'required',
            'id_tipo_condicion_alumno' => 'required',
            'fecha' => 'required | date',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $id_materia = $request->input('id_materia');
        $asistencia = $request->input('asistencia');
        $nota = $request->input('nota');
        $nota_nombre = $request->input('nota_nombre');
        $id_tipo_condicion_alumno = $request->input('id_tipo_condicion_alumno');
        $fecha = Carbon::parse($request->input('fecha'));
        $libro = $request->input('libro');
        $folio = $request->input('folio');
        $observaciones = $request->input('observaciones');

        $alumno = AlumnoMateriaNota::find($id_alumno_materia_nota);
        $alumno->id_materia = $id_materia;
        $alumno->asistencia = $asistencia;
        $alumno->nota = $nota;
        $alumno->nota_nombre = $nota_nombre;
        $alumno->id_tipo_condicion_alumno = $id_tipo_condicion_alumno;
        $alumno->fecha = $fecha;
        $alumno->libro = $libro;
        $alumno->folio = $folio;
        $alumno->observaciones = $observaciones;
        $alumno->save();

        event(new RegistracionAlumnoMateriaNota($alumno));
        return response()->json($alumno,200);
    }


    public function destroy(Request $request){
        $user = Auth::user();
        $id_alumno_materia_nota = $request->route('id_alumno_materia_nota');

        $alumno = AlumnoMateriaNota::find($id_alumno_materia_nota);
        $alumno->estado = 0;
        $alumno->save();

        event(new RegistracionAlumnoMateriaNota($alumno));
        return response()->json($alumno,200);
    }

    public function importar(Request $request){
        $id_inscripcion = $request->route('id_inscripcion');
        $user = Auth::user();
        $salida = [];
        $import = new AlumnoMateriaNotaImport($id_inscripcion,$user->id);
        if($request->hasFile('archivo')){
            $salida = Excel::import($import, $request->file('archivo'));
        }
        return response()->json($salida,200);
    }

    public function importar_previa(Request $request){
        $id_inscripcion = $request->route('id_inscripcion');
        $user = Auth::user();
        $salida = [];
        $import = new AlumnoMateriaNotaImport($id_inscripcion,$user->id);
        if($request->hasFile('archivo')){
            $array = $import->toArray($request->file('archivo'))[0];
            foreach ($array as $row) {
                if(
                    $row['materia']==null and 
                    $row['nota']==null and 
                    $row['nota_nombre']==null and 
                    $row['condicionalidad']==null and 
                    $row['fecha']==null and 
                    $row['observaciones']==null and
                    $row['folio']==null and
                    $row['libro']==null
                ) {

                } else {
                    $id_condicionalidad = null;
                    if($row['condicionalidad'] != null){
                        $condicionalidad = TipoCondicionAlumno::where('estado',1)
                            ->where('nombre','like',$row['condicionalidad'])
                            ->first();
                        if($condicionalidad){
                            $id_condicionalidad = $condicionalidad->id;
                        } 
                    }
                    if($row['fecha'] != null){
                        try {
                            $fecha = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha'])->format('Y-m-d');
                        } catch(\Exception $e){
                            $fecha = null;
                        }
                        
                    }
                    $id_materia = null;
                    if($row['materia']!=null){
                        $materia = Materia::where('estado',1)
                            ->where(function($q)use($row){
                                $q->where('nombre','like',$row['materia'])
                                    ->orWhere('codigo','like',$row['materia']);
                            })
                            ->first();
                        if($materia){
                            $id_materia = $materia->id;
                        }
                    }
                    $row['fecha'] = $fecha;
                    $row['id_materia'] = $id_materia;
                    $row['id_condicionalidad'] = $id_condicionalidad;
                    $salida[] = $row;
                }
                
            }
        }
        return response()->json($salida,200);
    }

    public function importar_ejemplo(Request $request){
        $export = new AlumnoMateriaNotaExampleExport;
        $export->custom();
        return $export->download('notas_ejemplo.xlsx');
    }

    public function reporte(Request $request){
        $id_sede = $request->route('id_sede');
        $id_inscripcion = $request->route('id_inscripcion');
        $inscripcion = Inscripcion::find($id_inscripcion);

        $jasper = new JasperPHP;
        $input = storage_path("app/reportes/alumno_inscripcion_analitico.jasper");
        $output = storage_path("app/reportes/".uniqid());
        $ext = "pdf";

        $jasper->process(
            $input,
            $output,
            [$ext],
            [
                'id_inscripcion' => $id_inscripcion,
                'logo'=> storage_path("app/images/logo_constancia.png")??null,
                'REPORT_LOCALE' => 'es_AR',
            ],
            \Config::get('database.connections.mysql')
        )->execute();
        
        $filename ='alumno_analitico-'.$inscripcion->alumno->documento;
        return response()->download($output . '.' . $ext, $filename,['Content-Type: application/pdf'])->deleteFileAfterSend();
    }
}
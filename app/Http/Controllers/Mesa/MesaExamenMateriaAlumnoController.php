<?php

namespace App\Http\Controllers\Mesa;

use App\User;
use App\Models\Mesa\MesaExamen;
use App\Models\Mesa\MesaExamenMateria;
use App\Models\Mesa\MesaExamenMateriaAlumno;
use App\Models\Mesa\TipoCondicionAlumno;
use App\Models\Carrera;
use App\Models\CarreraModalidad;
use App\Models\Materia;
use App\Models\Inscripcion;
use App\Models\Alumno;
use App\Models\Comision;
use App\Models\ComisionAlumno;
use App\Models\Asistencia;
use App\Models\Sede;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use Carbon\Carbon;

class MesaExamenMateriaAlumnoController extends Controller
{
    public function show(Request $request){
        $id_mesa_examen_materia_alumno = $request->route('id_mesa_examen_materia_alumno');

        $alumno = MesaExamenMateriaAlumno::with('alumno','condicion')->find($id_mesa_examen_materia_alumno);

        return response()->json($alumno,200);
    }

    public function update(Request $request){
        $id_mesa_examen_materia_alumno = $request->route('id_mesa_examen_materia_alumno');

        $validator = Validator::make($request->all(),[
            'asistencia' => 'required',
            'nota' => 'required',
            'nota_nombre' => 'required',
            'id_tipo_condicion_alumno' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $asistencia = $request->input('asistencia');
        $nota = $request->input('nota');
        $nota_nombre = $request->input('nota_nombre');
        $id_tipo_condicion_alumno = $request->input('id_tipo_condicion_alumno');
        $observaciones = $request->input('observaciones');

        $alumno = MesaExamenMateriaAlumno::with('alumno','condicion')->find($id_mesa_examen_materia_alumno);
        $alumno->asistencia = $asistencia;
        $alumno->nota = $nota;
        $alumno->nota_nombre = $nota_nombre;
        $alumno->id_tipo_condicion_alumno = $id_tipo_condicion_alumno;
        $alumno->observaciones = $observaciones;
        $alumno->save();

        $mesa_examen_materia = MesaExamenMateria::find($alumno->id_mesa_examen_materia);
        $alumnos_cantidad = MesaExamenMateriaAlumno::selectRaw('count(*) as total, SUM(IF(mam_nota<4,1,0)) as no_aprobado, SUM(IF(mam_nota>3,1,0)) as aprobado')
            ->where([
                'estado' => 1,
                'mma_id' => $alumno->id_mesa_examen_materia,
            ])
            ->whereNotNull('nota')
            ->groupBy('mma_id')->first();
        $mesa_examen_materia->alumnos_cantidad_aprobado = $alumnos_cantidad->aprobado??0;
        $mesa_examen_materia->alumnos_cantidad_no_aprobado = $alumnos_cantidad->no_aprobado??0;
        $mesa_examen_materia->save();

        return response()->json($alumno,200);
    }

/*
    public function destroy(Request $request){
        $user = Auth::user();
        $id_mesa_examen_materia_alumno = $request->route('id_mesa_examen_materia_alumno');

        $alumno = MesaExamenMateriaAlumno::with('alumno','condicion')->find($id_mesa_examen_materia_alumno);
        $alumno->estado = 0;
        $alumno->usu_id_baja = $asistencia;
        $alumno->deleted_at = Carbon::now();
        $alumno->save();

        return response()->json($alumno,200);
    }
*/
}
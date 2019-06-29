<?php
namespace App\Imports;

use App\Models\Mesa\AlumnoMateriaNota;
use App\Models\Mesa\TipoCondicionAlumno;
use App\Models\Inscripcion;
use App\Models\Materia;
use App\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class AlumnoMateriaNotaImport implements ToModel, WithHeadingRow
{
	use Importable;

    public function __construct(int $id_inscripcion,int $id_usuario)
    {
        $this->id_inscripcion = $id_inscripcion;
        $this->id_usuario = $id_usuario;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function model(array $row)
    {
        if($row['condicionalidad']!=null){
            $condicionalidad = TipoCondicionAlumno::where('estado',1)
                ->where('nombre','like',$row['condicionalidad'])
                ->first();
            if($condicionalidad){
                $id_tipo_condicion_alumno = $condicionalidad->id;
            } else {
                return null;
            }   
        } else {
            return null;
        }
        
        $fecha = null;
        if($row['fecha']!=null){
            $fecha = Carbon::parse($row['fecha']);
        }

        if($row['materia']!=null){
            $materia = Materia::where('estado',1)
                ->where(function($q)use($row){
                    $q->where('nombre','like',$row['materia'])
                        ->orWhere('codigo','like',$row['materia']);
                })
                ->first();
            if($materia){
                $id_materia = $materia->id;
            } else {
                return null;
            }
        } else {
            return null;
        }
        
        $inscripcion = Inscripcion::find($this->id_inscripcion);
        $nota = new AlumnoMateriaNota;
        $nota->id_inscripcion = $inscripcion->id;
        $nota->id_alumno = $inscripcion->id_alumno;
        $nota->id_materia = $id_materia;
        $nota->id_tipo_condicion_alumno = $id_tipo_condicion_alumno;
        $nota->nota = $row['nota'];
        $nota->nota_nombre = $row['nota_nombre'];
        $nota->asistencia = true;
        $nota->fecha = $fecha;
        $nota->observaciones = $row['observaciones'];
        $nota->libro = $row['libro'];
        $nota->folio = $row['folio'];
        $nota->id_usuario = $this->id_usuario;
        $nota->save();
        return $nota;
    }
}
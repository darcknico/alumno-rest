<?php
namespace App\Imports;

use App\User;
use App\Models\TipoAsistenciaAlumno;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMappedCells;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToModel;

class AsistenciaImport implements WithMappedCells, WithHeadingRow, ToModel
{
	use Importable;

	public function mapping(): array
    {
        return [
            'apellido'  => 'A2',
            'nombre' => 'B2',
            'documento' => 'C2',
            'asistencia' => 'D2',
            'observaciones' => 'E2',
        ];
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function model(array $row)
    {
        $tipo_asistencia = TipoAsistenciaAlumno::where('nombre','like',$row->asistencia)->first();
        if($tipo_asistencia){
            $row['id_tipo_asistencia_alumno'] = $tipo_asistencia->id;
        } else {
            $row['id_tipo_asistencia_alumno'] = 0;
        }
        return $row;
    }
    
}
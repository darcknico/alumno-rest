<?php

namespace App\Exports;
use App\Models\Alumno;
use App\Models\Comision;
use App\Models\Materia;
use App\Models\Carrera;
use App\Models\PlanPago;
use App\Models\Inscripcion;
use App\Models\Academico\Docente;
use App\Models\Academico\DocenteMateria;
use App\Filters\ComisionFilter;

use Carbon\Carbon;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ComisionExport implements ShouldAutoSize, FromCollection, WithMapping, WithHeadings
{
    use Exportable;
 
    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $filters = $this->filters;

        $registros = Comision::with('carrera','materia','modalidad')
            ->where('estado',1);

        $registros = ComisionFilter::fill($filters,$registros);
        return $registros->orderBy('created_at','desc')->get();
    }
 
    public function headings(): array
    {
        return [
            '#',
            'Aula Virtual Id',
            'Carrera',
            'Materia',
            'Modalidad',

            'Numero',
            'Año',
            'Inscriptos',
            'Responsable',
            'Fecha Comienzo',
            'Fecha Finalizacion',
        ];
    }

    public function map($registro): array
    {
    	
        return [
            $registro->id,
            $registro->id_aula_virtual,
            $registro->carrera->nombre,
            $registro->materia->nombre,
            $registro->modalidad->nombre,
            $registro->numero,
            $registro->anio,
            $registro->cantidad,
            $registro->responsable_apellido.', '.$registro->responsable_nombre,
            $registro->clase_inicio,
            $registro->clase_final,
        ];
    }
}

<?php

namespace App\Exports;
 
use App\Models\Alumno;
use App\Models\PlanPago;
use App\Models\Inscripcion;
use App\Models\Carrera;
use App\Filters\InscripcionFilter;

use Carbon\Carbon;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
 
class InscripcionExport implements ShouldAutoSize, FromCollection, WithMapping, WithHeadings
{
    use Exportable;
 
    public function __construct(int $id_sede,$filters)
    {
        $this->id_sede = $id_sede;
        $this->filters = $filters;
    }

    public function collection()
    {
        $id_sede = $this->id_sede;
        $filters = $this->filters;

        $registros = Inscripcion::where([
            'sed_id' => $id_sede,
            'estado' => 1,
        ]);
        $registros = InscripcionFilter::fill($filters,$registros);
        return $registros->orderBy('car_id','desc')->orderBy('anio','asc')->get();
    }
 
    public function headings(): array
    {
        return [
            'Tipo',
            'Documento',
            'Apellido',
            'Nombre',
            'Email',
            'Fecha Nacimiento',
            'Departamento',
            'Carrera',
            'Plan de Estudio',
            'Beca',
            'Año Inscripcion',
            'Estado',
            'Observaciones',
            'Alta',
            'Año Lectivo',
            'Porcentaje Aprobado',
        ];
    }

    public function map($registro): array
    {
        $beca = "";
        if($registro->beca){
            $beca = $registro->beca->nombre;
        }
        $alumno = $registro->alumno;
        $tipo_documento = "";
        if($alumno->tipoDocumento){
            $tipo_documento = $alumno->tipoDocumento->nombre;
        }
        $fecha_nacimiento = "";
        if($alumno->fecha_nacimiento){
            $fecha_nacimiento = Carbon::parse($alumno->fecha_nacimiento)->format('d/m/Y');
        }
        return [
            $tipo_documento,
            $alumno->documento,
            $alumno->apellido,
            $alumno->nombre,
            $alumno->email,
            $fecha_nacimiento,
            $registro->carrera->departamento->nombre,
            $registro->carrera->nombre,
            $registro->plan_estudio->nombre,
            $beca,
            $registro->anio,
            $registro->tipo_estado->nombre,
            $registro->observaciones,
            $registro->created_at,
            $registro->id_periodo_lectivo,
            $registro->porcentaje_aprobados,
        ];
    }
 
}
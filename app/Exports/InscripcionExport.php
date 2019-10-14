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
        return ['Documento','Apellido','Nombre','Departamento','Carrera','Plan de Estudio','Beca','Año Inscripcion','Estado','Observaciones','Alta'];
    }

    public function map($registro): array
    {
        $beca = "";
        if($registro->beca){
            $beca = $registro->beca->nombre;
        }
        return [
            $registro->alumno->documento,
            $registro->alumno->apellido,
            $registro->alumno->nombre,
            $registro->carrera->departamento->nombre,
            $registro->carrera->nombre,
            $registro->plan_estudio->nombre,
            $beca,
            $registro->anio,
            $registro->tipo_estado->nombre,
            $registro->observaciones,
            $registro->created_at,
        ];
    }
 
}
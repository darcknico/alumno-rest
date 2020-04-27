<?php

namespace App\Exports;
use App\Models\Alumno;
use App\Models\Materia;
use App\Models\Carrera;
use App\Models\PlanPago;
use App\Models\Inscripcion;
use App\Models\Academico\Docente;
use App\Models\Academico\DocenteMateria;
use App\Filters\DocenteMateriaFilter;

use Carbon\Carbon;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DocenteAsignacionExport implements ShouldAutoSize, FromCollection, WithMapping, WithHeadings
{
    use Exportable;
 
    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $filters = $this->filters;

        $registros = DocenteMateria::with('docente','sede')
            ->where('estado',1);

        $registros = DocenteMateriaFilter::fill($filters,$registros);
        return $registros->orderBy('created_at','desc')->get();
    }
 
    public function headings(): array
    {
        return [
            'CUIT',
            'Apellido',
            'Nombre',
            'Titulo',
            'Contratos',

            'Tipo Documento',
            'Documento',
            
            'Materia',
            'Carrera',
            'Cargo',
            'H/Catedra',
            'Fecha Asignacion',
        ];
    }

    public function map($registro): array
    {
        $beca = "";
        if($registro->beca){
            $beca = $registro->beca->nombre;
        }
        $usuario = $registro->usuario;
        $tipo_documento = "";
        if($usuario->tipoDocumento){
            $tipo_documento = $usuario->tipoDocumento->nombre;
        }
        $fecha_asignacion = "";
        if($registro->fecha_asignacion){
            $fecha_asignacion = Carbon::parse($registro->fecha_asignacion)->format('d/m/Y');
        }

        $contratos = "";
        if($registro->contratos){
            foreach ($registro->contratos as $contrato) {
                $contratos = $contratos . " " . $contrato->tipo->nombre;
            }
        }
        $cargo = "";
        if($registro->cargo){
            $cargo = $registro->cargo->nombre;
        }
    	
        return [
            $registro->cuit,
            $usuario->apellido,
            $usuario->nombre,
            $registro->titulo,
            $contratos,
            $tipo_documento,
            $usuario->documento,
            
            $registro->materia->nombre,
            $registro->carrera->nombre,
            $cargo,
            $registro->horas_catedra,
            $fecha_asignacion,
        ];
    }
}

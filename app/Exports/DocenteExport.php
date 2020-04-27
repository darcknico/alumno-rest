<?php

namespace App\Exports;

use App\Models\Alumno;
use App\Models\PlanPago;
use App\Models\Inscripcion;
use App\Models\Academico\Docente;
use App\Filters\DocenteFilter;

use Carbon\Carbon;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DocenteExport implements ShouldAutoSize, FromCollection, WithMapping, WithHeadings
{
    use Exportable;
 
    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $filters = $this->filters;

        $registros = Docente::whereHas('usuario',function($q){
            $q->where('id_tipo_usuario',8);
        });

        $registros = DocenteFilter::fill($filters,$registros);
        return $registros->orderBy('cuit','desc')->get();
    }
 
    public function headings(): array
    {
        return [
            'CUIT',
            'Apellido',
            'Nombre',
            'Titulo',
            'Contratos',

            'Correo',
            'Fecha Nacimiento',
            'Tipo Documento',
            'Documento',
            'Telefono',
            'Celular',
            'Direccion',
            'Numero',
            'Depto',
            'Piso',

            'Observaciones',
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
        $fecha_nacimiento = "";
        if($usuario->fecha_nacimiento){
            $fecha_nacimiento = Carbon::parse($usuario->fecha_nacimiento)->format('d/m/Y');
        }
        $contratos = "";
    	foreach ($registro->contratos as $contrato) {
    		$contratos = $contratos . " " . $contrato->tipo->nombre;
    	}
        return [
            $registro->cuit,
            $usuario->apellido,
            $usuario->nombre,
            $registro->titulo,
            $contratos,

            $usuario->documento,
            
            $usuario->email,
            $fecha_nacimiento,
            $tipo_documento,
            $usuario->documento,
            $usuario->telefono,
            $usuario->celular,
            $usuario->direccion,
            $usuario->direccion_numero,
            $usuario->direccion_dpto,
            $usuario->direccion_piso,

            $registro->observaciones,
        ];
    }
}

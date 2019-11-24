<?php

namespace App\Exports;
 
use App\Models\Alumno;
use App\Models\PlanPago;
use App\Models\Inscripcion;
use App\Models\Carrera;
use App\Models\Pago;

use App\Filters\PlanPagoFilter;

use Carbon\Carbon;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
 
class PlanPagoExport implements ShouldAutoSize, FromArray, WithMapping, WithHeadings, WithColumnFormatting
{
    use Exportable;
 
    public function __construct(
        int $id_sede, $search, int $id_departamento,int $id_carrera,$deudores,$id_tipo_materia_lectivo,$anio,$id_tipo_inscripcion_estado
    )
    {
        $this->id_sede = $id_sede;
        $this->id_departamento = $id_departamento;
        $this->id_carrera = $id_carrera;
        $this->deudores = $deudores;
        $this->search = $search;
        $this->id_tipo_materia_lectivo = $id_tipo_materia_lectivo;
        $this->anio = $anio;
        $this->id_tipo_inscripcion_estado = $id_tipo_inscripcion_estado;
        $this->enumeracion = 1;
    }

    public function array(): array
    {
        $id_sede = $this->id_sede;
        $id_departamento = $this->id_departamento;
        $id_carrera = $this->id_carrera;
        $deudores = $this->deudores;
        $search = $this->search;
        $id_tipo_materia_lectivo = $this->id_tipo_materia_lectivo;
        $anio = $this->anio;
        $id_tipo_inscripcion_estado = $this->id_tipo_inscripcion_estado;
        $registros = PlanPago::with([
          'inscripcion.alumno',
          'inscripcion.carrera',
          'inscripcion.beca',
          'inscripcion.tipo_estado',
        ])->where([
          'sed_id' => $id_sede,
          'estado' => 1,
        ]);

        $registros = PlanPagoFilter::query(
            $search,
            $id_departamento,
            $id_carrera,
            $id_tipo_materia_lectivo,
            $anio,
            $deudores,
            $id_tipo_inscripcion_estado,
            $registros
          );
        $registros = $registros->orderBy('anio','desc')->get()->toArray();
        usort($registros, function($a1,$a2){
            return strcmp($a1['inscripcion']['alumno']['apellido'],$a2['inscripcion']['alumno']['apellido']);
        });
        return $registros;
    }
 
    public function headings(): array
    {
        return ['N°','Fecha','Alumno','Carrera','Año','Total Cuota','Pagado','Saldo Total','Saldo Hoy','Beca','Estado'];
    }

    public function map($registro): array
    {
        $inscripcion = $registro['inscripcion'];
        $alumno = "";
        $beca = "";
        $estado = "";
        if($inscripcion){
            $alumno = $inscripcion['alumno']['apellido'] .", ".$inscripcion['alumno']['apellido'];
            $beca = $inscripcion['beca']['nombre'];
            $estado = $inscripcion['tipo_estado']['nombre'];
        }
        $enumeracion = $this->enumeracion;
        $this->enumeracion = $this->enumeracion + 1;
        return [
            $enumeracion,
            Carbon::parse($registro['created_at'])->format('d/m/Y'),
            $alumno,
            $inscripcion['carrera']['nombre'],
            $registro['anio'],
            $registro['cuota_total'],
            $registro['pagado'],
            $registro['saldo_total'],
            $registro['saldo_hoy'],
            $beca,
            $estado,
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'F' => "$#,##0.00",
            'G' => "$#,##0.00",
            'H' => "$#,##0.00",
            'I' => "$#,##0.00",
        ];
    }
 
}
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
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
 
class PlanPagoExport implements ShouldAutoSize, FromCollection, WithMapping, WithHeadings, WithColumnFormatting
{
    use Exportable;
 
    public function __construct(int $id_sede, $search, int $id_departamento,int $id_carrera,$deudores,$id_tipo_materia_lectivo,$anio)
    {
        $this->id_sede = $id_sede;
        $this->id_departamento = $id_departamento;
        $this->id_carrera = $id_carrera;
        $this->deudores = $deudores;
        $this->search = $search;
        $this->id_tipo_materia_lectivo = $id_tipo_materia_lectivo;
        $this->anio = $anio;
    }

    public function collection()
    {
        $id_sede = $this->id_sede;
        $id_departamento = $this->id_departamento;
        $id_carrera = $this->id_carrera;
        $deudores = $this->deudores;
        $search = $this->search;
        $id_tipo_materia_lectivo = $this->id_tipo_materia_lectivo;
        $anio = $this->anio;
        $registros = PlanPago::with([
          'inscripcion.alumno',
          'inscripcion.carrera',
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
            $registros
          );

        return $registros->orderBy('anio','desc')->get();
    }
 
    public function headings(): array
    {
        return ['Fecha','Alumno','Carrera','Año','Total Cuota','Pagado','Saldo Total','Saldo Hoy'];
    }

    public function map($registro): array
    {
        $inscripcion = Inscripcion::find($registro->id_inscripcion);
        $alumno = "";
        if($inscripcion){
            $alumno = $inscripcion->alumno->apellido." ".$inscripcion->alumno->nombre;
        }

        return [
            Carbon::parse($registro->created_at)->format('d/m/Y'),
            $alumno,
            $inscripcion->carrera->nombre,
            $registro->anio,
            $registro->cuota_total,
            $registro->pagado,
            $registro->saldo_total,
            $registro->saldo_hoy,
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'E' => "$#,##0.00",
            'F' => "$#,##0.00",
            'G' => "$#,##0.00",
            'H' => "$#,##0.00",
        ];
    }
 
}
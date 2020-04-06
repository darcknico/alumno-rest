<?php

namespace App\Exports;
 
use App\Models\Alumno;
use App\Models\PlanPago;
use App\Models\Inscripcion;
use App\Models\Carrera;
use App\Models\Pago;
use App\Models\Sede;

use App\Filters\PlanPagoFilter;

use Carbon\Carbon;

use Maatwebsite\Excel\Facades\Excel;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
 
class PlanPagoExport implements ShouldAutoSize, FromArray, WithMapping, WithHeadings, WithColumnFormatting
{
    use Exportable;
 
    public function __construct(int $id_sede, $array)
    {
        $this->id_sede = $id_sede;
        $this->array = $array;
        $this->enumeracion = 1;
    }

    public function array(): array
    {
        $id_sede = $this->id_sede;
        $array = $this->array;
        $registros = PlanPago::with([
          'inscripcion.alumno',
          'inscripcion.carrera',
          'inscripcion.beca',
          'inscripcion.tipo_estado',
        ])->where([
          'sed_id' => $id_sede,
          'estado' => 1,
        ]);

        $registros = PlanPagoFilter::fill(
            $array,
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
        $saldo_hoy = $registro['saldo_hoy'];
        if($saldo_hoy<0){
            $saldo_hoy = 0;
        }
        return [
            $enumeracion,
            Carbon::parse($registro['created_at'])->format('d/m/Y'),
            $alumno,
            $inscripcion['carrera']['nombre'],
            $registro['anio'],
            $registro['cuota_total'],
            $registro['pagado'],
            $registro['saldo_total'],
            $saldo_hoy,
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
    
    public function custom(){
        $sede = Sede::find($this->id_sede);
        Excel::extend(static::class, function (PlanPagoExport $export, $writer)use($sede){
            $sheet = $writer->getSheetByIndex(0);
            
            $styleArray = array(
                'borders' => array(
                    'outline' => array(
                        'borderStyle' => Border::BORDER_THICK,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            );
            $inicio = 1;
            $sheet->getStyle('A'.$inicio.':K'.$inicio.'')->applyFromArray($styleArray);
            $ultima = $sheet->getHighestRow();
            $sheet->getStyle('A2:K'.$ultima++)->applyFromArray($styleArray);
            $sheet->SetCellValue("C".$ultima, 'TOTAL');
            $sheet->SetCellValue("F".$ultima, '=SUM(F'.$inicio.':F'.$ultima.')');
            $sheet->SetCellValue("G".$ultima, '=SUM(G'.$inicio.':G'.$ultima.')');
            $sheet->SetCellValue("H".$ultima, '=SUM(H'.$inicio.':H'.$ultima.')');
            $sheet->SetCellValue("I".$ultima, '=SUM(I'.$inicio.':I'.$ultima.')');
            $sheet->getStyle('F'.$ultima.':I'.$ultima)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
            $ultima = $ultima + 2;
            $sheet->SetCellValue("C".$ultima, 'Sede:');
            $sheet->SetCellValue("D".$ultima, $sede->nombre);

        });
    }
}
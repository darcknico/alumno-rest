<?php

namespace App\Exports;
 
use App\Models\Diaria;
use App\Models\Pago;
use App\Models\Movimiento;
use App\Models\Mesa\TipoCondicionAlumno;

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
use Maatwebsite\Excel\Concerns\WithTitle;

class DiariaFormaExport implements ShouldAutoSize, FromArray, WithMapping, WithHeadings, WithColumnFormatting, WithTitle
{
    use Exportable;
 
    public function __construct(Diaria $diaria,$forma = [1])
    {
        $this->diaria = $diaria;
        $this->forma = $forma;
    }

    public function array(): array
    {
        $diaria = $this->diaria;
        $ingresos = Movimiento::with('tipo','forma')->where([
            'estado' => 1,
            'sed_id' => $diaria->id_sede
        ])
        ->whereDate('fecha','>=',$diaria->fecha_inicio)
        ->whereIn('id_forma_pago',$this->forma)
        ->when(!empty($diaria->fecha_fin),function($q)use($diaria){
            return $q->whereDate('fecha','<=',$diaria->fecha_fin);
        })
        ->orderBy('created_at','asc')
        ->get();
        $movimientos = [];
        foreach ($ingresos as $movimiento) {
            $pago = Pago::where('mov_id',$movimiento->id)->first();
            if($pago){
                $movimiento['pago'] = $pago;
                if($pago->plan_pago){
                    $movimiento['alumno'] = $pago->plan_pago->inscripcion->alumno;
                } else {
                    $movimiento['alumno'] = $pago->inscripcion->alumno;
                }
            }
            $movimientos[] = $movimiento;
        }
        return $movimientos;
    }
 
    public function headings(): array
    {
        return [
            'FECHA',
            'CONCEPTO/NOMBRE DEL ALUMNO',
            'CUOTA N° - N° COMPROBANTE',
            'TIPO - FORMA',
            'INGRESO',
            'EGRESO',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'E' => "$#,##0.00",
            'F' => "$#,##0.00",
        ];
    }

    public function map($registro): array
    {
        $ingreso = '';
        $egreso = '';
        if($registro->id_tipo_egreso_ingreso==1){
            $ingreso = $registro->monto;
        } else {
            $egreso = $registro->monto;
        }
        $concepto = $registro->descripcion;
        $cuota = '';
        $numero = '';
        if(!is_null($registro['alumno'])){
            $concepto = $registro['alumno']->apellido." ".$registro['alumno']->nombre;
            $cuota = $registro['pago']->descripcion;
            $numero = $registro['pago']->numero_oficial;
        } else {
            $tipo = '';
            if ($registro->tipo_comprobante) {
                $tipo = $registro->tipo_comprobante->nombre.':';
            }
            $numero = $tipo.$registro->numero;
        }
        return [
            Date::dateTimeToExcel(Carbon::parse($registro->fecha)),
            $concepto,
            $cuota.' - '.$numero,
            $registro->tipo->nombre.' '.$registro->forma->nombre,
            $ingreso,
            $egreso,
        ];
    }

    public function title(): string
    {
        if(count($this->forma)>1){
            return 'Otros';
        }
        return 'Efectivo';
    }
 
    public function custom(){
        $diaria = $this->diaria;
        Excel::extend(static::class, function (DiariaFormaExport $export, $writer)use($diaria){
            $sheet = $writer->getSheetByIndex(0);
            
            $styleArray = array(
                'borders' => array(
                    'outline' => array(
                        'borderStyle' => Border::BORDER_THICK,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            );
            $sheet->getStyle('A1:F1')->applyFromArray($styleArray);
            $ultima = $sheet->getHighestRow();
            $sheet->getStyle('A2:F'.$ultima++)->applyFromArray($styleArray);
            if(count($this->forma)>1){
                $sheet->SetCellValue("E".$ultima, $diaria->total_otros_ingreso);
                $sheet->SetCellValue("F".$ultima, $diaria->total_otros_egreso);
            } else {
                $sheet->SetCellValue("E".$ultima, $diaria->total_ingreso);
                $sheet->SetCellValue("F".$ultima, $diaria->total_egreso);
            }
            
            $sheet->getStyle('E'.$ultima.':F'.$ultima)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
            $sheet->getStyle('E'.$ultima.':F'.$ultima)->applyFromArray($styleArray);
            $ultima = $ultima + 2;
            $sheet->SetCellValue("E".$ultima, "SALDO AL INCIO");
            if(count($this->forma)>1){
                $sheet->SetCellValue("F".$ultima++, $diaria->saldo_otros_anterior);
            } else {
                $sheet->SetCellValue("F".$ultima++, $diaria->saldo_anterior);
            }
            $sheet->SetCellValue("E".$ultima, "SALDO AL CIERRE");
            if(count($this->forma)>1){
                $sheet->SetCellValue("F".$ultima, $diaria->saldo_otros);
            } else {
                $sheet->SetCellValue("F".$ultima, $diaria->saldo);
            }
            $sheet->getStyle('F'.($ultima-1).':F'.$ultima)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
            $sheet->getStyle('F'.($ultima-1).':F'.$ultima)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F28A8C');
        });
    }
}
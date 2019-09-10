<?php

namespace App\Exports;
 
use App\Models\PlanPago;
use App\Models\Carrera;
use App\Models\Sede;
use App\Models\TipoMateriaLectivo;

use Carbon\Carbon;

use Maatwebsite\Excel\Facades\Excel;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;
use \DB;

class AlumnoPlanPagoExport implements ShouldAutoSize, FromCollection, WithMapping, WithHeadings, WithColumnFormatting, WithTitle
{
    use Exportable;
 
    public function __construct($id_sede,$anio,$id_carrera,$id_tipo_materia_lectivo)
    {
        $this->id_sede = $id_sede;
        $this->anio = $anio;
        $this->id_carrera = $id_carrera;
        $this->id_tipo_materia_lectivo = $id_tipo_materia_lectivo;
        $this->carrera = Carrera::find($id_carrera);
        $this->sede = Sede::find($id_sede);
        $this->tipo = TipoMateriaLectivo::find($id_tipo_materia_lectivo);
    }

    public function collection()
    {
        $id_sede = $this->id_sede;
        $anio = $this->anio;
        $id_carrera = $this->id_carrera;
        $id_tipo_materia_lectivo = $this->id_tipo_materia_lectivo;

        $registros = PlanPago::where([
            'ppa_anio' => $anio,
            'sed_id' => $id_sede,
            'estado' => 1,
        ])->whereHas('inscripcion',function($q)use($id_carrera,$id_tipo_materia_lectivo,$anio){
            $q->where([
                'car_id' => $id_carrera,
                'estado' => 1,
            ])->whereHas('comisiones',function($qt)use($id_tipo_materia_lectivo,$anio){
                $qt->where('estado',1)
                    ->whereHas('comision',function($qtr)use($id_tipo_materia_lectivo,$anio){
                        $qtr->where([
                                'estado' => 1,
                                'com_anio' => $anio,
                            ])
                            ->whereHas('materia',function($qtrs)use($id_tipo_materia_lectivo){
                                $qtrs->where('estado',1)->where('id_tipo_materia_lectivo',$id_tipo_materia_lectivo);
                            });
                    });
            });
        })
        ->get();
        return $registros;
    }
 
    public function headings(): array
    {
        return [
            'ALUMNO',
            'FEBRERO',
            'MARZO',
            'ABRIL',
            'MAYO',
            'JUNIO',
            'JULIO',
            'AGOSTO',
            'SEPTIEMBRE',
            'OCTUBRE',
            'NOVIEMBRE',
            'DICIEMBRE',
            'TOTAL',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => "$#,##0.00",
            'C' => "$#,##0.00",
            'D' => "$#,##0.00",
            'E' => "$#,##0.00",
            'F' => "$#,##0.00",
            'G' => "$#,##0.00",
            'H' => "$#,##0.00",
            'I' => "$#,##0.00",
            'J' => "$#,##0.00",
            'K' => "$#,##0.00",
            'L' => "$#,##0.00",
            'M' => "$#,##0.00",
        ];
    }

    public $index = 1;
    public function map($registro): array
    {
        $alumno = $registro->inscripcion->alumno->apellido.', '.$registro->inscripcion->alumno->nombre;
        $results = DB::select("
                SELECT 
                    cobranza_mes(ppa_id,2) as febrero,
                    cobranza_mes(ppa_id,3) as marzo,
                    cobranza_mes(ppa_id,4) as abril,
                    cobranza_mes(ppa_id,5) as mayo,
                    cobranza_mes(ppa_id,6) as junio,
                    cobranza_mes(ppa_id,7) as julio,
                    cobranza_mes(ppa_id,8) as agosto,
                    cobranza_mes(ppa_id,9) as septiembre,
                    cobranza_mes(ppa_id,10) as octubre,
                    cobranza_mes(ppa_id,12) as noviembre,
                    cobranza_mes(ppa_id,12) as diciembre
                FROM tbl_planes_pago where ppa_id = ?;
                ", [
            $this->id_sede,
            ]
            );
        $result = [];
        if(count($results)>0){
            $result = $results[0];
        }
        $febrero = $result->febrero??0;
        $marzo = $result->marzo??0;
        $abril = $result->abril??0;
        $mayo = $result->mayo??0;
        $junio = $result->junio??0;
        $julio = $result->julio??0;
        $agosto = $result->agosto??0;
        $septiembre = $result->septiembre??0;
        $octubre = $result->octubre??0;
        $noviembre = $result->noviembre??0;
        $diciembre = $result->diciembre??0;
        $this->index = $this->index + 1;
        return [
            $alumno,
            $febrero,
            $marzo,
            $abril,
            $mayo,
            $junio,
            $julio,
            $agosto,
            $septiembre,
            $octubre,
            $noviembre,
            $diciembre,
            '=SUM(B'.$this->index.':M'.$this->index.')'
            //$febrero + $marzo + $abril + $mayo + $junio + $julio + $agosto + $septiembre + $octubre + $noviembre + $diciembre,
        ];
    }

    public function title(): string
    {
        return 'detalles cuotas';
    }
 
    public function custom(){
        $carrera = $this->carrera;
        $sede = $this->sede;
        $tipo = $this->tipo;
        $anio = $this->anio;
        Excel::extend(static::class, function (AlumnoPlanPagoExport $export, $writer)use($carrera,$sede,$tipo,$anio){
            $sheet = $writer->getSheetByIndex(0);
            
            $styleArray = array(
                'borders' => array(
                    'outline' => array(
                        'borderStyle' => Border::BORDER_THICK,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            );
            $sheet->getStyle('A1:M1')->applyFromArray($styleArray);
            $ultima = $sheet->getHighestRow();
            $sheet->getStyle('A2:M'.($ultima+1))->applyFromArray($styleArray);
            $ultima++;
            $sheet->SetCellValue('A'.$ultima,'TOTAL');
            $sheet->SetCellValue('B'.$ultima,'=SUM(B2:B'.($ultima-1).')');
            $sheet->SetCellValue('C'.$ultima,'=SUM(C2:C'.($ultima-1).')');
            $sheet->SetCellValue('D'.$ultima,'=SUM(D2:D'.($ultima-1).')');
            $sheet->SetCellValue('E'.$ultima,'=SUM(E2:E'.($ultima-1).')');
            $sheet->SetCellValue('F'.$ultima,'=SUM(F2:F'.($ultima-1).')');
            $sheet->SetCellValue('G'.$ultima,'=SUM(G2:G'.($ultima-1).')');
            $sheet->SetCellValue('H'.$ultima,'=SUM(H2:H'.($ultima-1).')');
            $sheet->SetCellValue('I'.$ultima,'=SUM(I2:I'.($ultima-1).')');
            $sheet->SetCellValue('J'.$ultima,'=SUM(J2:J'.($ultima-1).')');
            $sheet->SetCellValue('K'.$ultima,'=SUM(K2:K'.($ultima-1).')');
            $sheet->SetCellValue('L'.$ultima,'=SUM(L2:L'.($ultima-1).')');
            $sheet->SetCellValue('M'.$ultima,'=SUM(M2:M'.($ultima-1).')');
            $sheet->getStyle('B'.$ultima.':M'.$ultima)
                ->getNumberFormat()
                ->setFormatCode("$#,##0.00");
            $ultima++;
            $ultima++;
            $sheet->SetCellValue('A'.$ultima++, 'DETALLE DE CUOTA COBRADAS POR CARRERA' );
            $sheet->SetCellValue('A'.$ultima++, $carrera->nombre );
            $sheet->SetCellValue('A'.$ultima++, 'Año de cursado: '.$tipo->nombre );
            $sheet->SetCellValue('A'.$ultima++, 'Año de pago: '.$anio );
            $sheet->SetCellValue('A'.$ultima++, $sede->nombre );
        });
    }
}
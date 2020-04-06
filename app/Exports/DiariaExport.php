<?php
namespace App\Exports;

use App\Models\Diaria;

use Carbon\Carbon;

use Maatwebsite\Excel\Facades\Excel;

use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DiariaExport implements WithMultipleSheets
{
    use Exportable;

    protected $diaria;
    
    public function __construct(int $id_diaria)
    {
        $this->diaria = Diaria::find($id_diaria);
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $diaria = new DiariaFormaExport($this->diaria);
        //$diaria->custom();
        $sheets[] = $diaria;
        $diaria = new DiariaFormaExport($this->diaria,[2,3,4,5]);
        //$diaria->custom();
        $sheets[] = $diaria;
        return $sheets;
    }

    public function custom(){
        $diaria = $this->diaria;
        Excel::extend(static::class, function (DiariaExport $export, $writer)use($diaria){
            //$sheet = $writer->getSheetByIndex(0);
            $sheetCount = $writer->getSheetCount();
            for ($i = 0; $i < $sheetCount; $i++) {
                $sheet = $writer->getSheet($i);
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()
                    ->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()
                    ->setScale(80);
                $sheet->getPageMargins()->setTop(1);
                $sheet->getPageMargins()->setRight(0.75);
                $sheet->getPageMargins()->setLeft(0.75);
                $sheet->getPageMargins()->setBottom(1);

                $styleArray = array(
                    'borders' => array(
                        'outline' => array(
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => array('argb' => 'FF000000'),
                        ),
                    ),
                );
                $sheet->getStyle('A1:F1')->applyFromArray($styleArray);
                $ultima = $sheet->getHighestRow();
                $sheet->getStyle('A2:F'.$ultima++)->applyFromArray($styleArray);
                if($i == 0){
                    $sheet->SetCellValue("E".$ultima, $diaria->total_ingreso);
                    $sheet->SetCellValue("F".$ultima, $diaria->total_egreso);
                } else {
                    $sheet->SetCellValue("E".$ultima, $diaria->total_otros_ingreso);
                    $sheet->SetCellValue("F".$ultima, $diaria->total_otros_egreso);
                }
                $sheet->getStyle('E'.$ultima.':F'.$ultima)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
                $sheet->getStyle('E'.$ultima.':F'.$ultima)->applyFromArray($styleArray);
                $ultima = $ultima + 2;
                $sheet->SetCellValue("E".$ultima, "SALDO AL INCIO");
                if($i == 0){
                    $sheet->SetCellValue("F".$ultima++, $diaria->saldo_anterior);
                } else {
                    $sheet->SetCellValue("F".$ultima++, $diaria->saldo_otros_anterior);
                }
                $sheet->SetCellValue("E".$ultima, "SALDO AL CIERRE");
                if($i == 0){
                    $sheet->SetCellValue("F".$ultima, $diaria->saldo);
                } else {
                    $sheet->SetCellValue("F".$ultima, $diaria->saldo_otros);
                }
                $sheet->getStyle('F'.($ultima-1).':F'.$ultima)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
                $sheet->getStyle('F'.($ultima-1).':F'.$ultima)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F28A8C');
            }
            
        });
    }
}

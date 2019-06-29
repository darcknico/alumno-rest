<?php

namespace App\Exports;
 
use App\Models\Alumno;
use App\Models\Mesa\TipoCondicionAlumno;

use Carbon\Carbon;

use Maatwebsite\Excel\Facades\Excel;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AlumnoMateriaNotaExampleExport implements ShouldAutoSize, FromCollection, WithMapping, WithHeadings
{
    use Exportable;
 
    public function __construct()
    {
    }

    public function collection()
    {
        $registros = Alumno::limit(5)->get();
        return $registros;
    }
 
    public function headings(): array
    {
        return [
            'materia',
            'nota',
            'nota_nombre',
            'condicionalidad',
            'fecha',
            'observaciones',
            'libro',
            'folio',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function map($registro): array
    {
        return [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];
    }
 
    public function custom(){
        Excel::extend(static::class, function (AlumnoMateriaNotaExampleExport $export, $writer){
            $tipos = TipoCondicionAlumno::where('estado',1)->get();
            $sheet = $writer->getSheetByIndex(0);
            $index = 1;
            foreach ($tipos as $tipo){
                $sheet->SetCellValue("O".$index, $tipo->nombre);
                $index = $index + 1;
            }
            $index = $index - 1;
            $styleArray = array(
                'borders' => array(
                    'outline' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            );
            $sheet ->getStyle('A1:H1')->applyFromArray($styleArray);

            for ($i=2; $i < 6 ; $i++) { 
                $cell = $sheet->getCell('D'.$i);
                $objValidation = $cell->getDataValidation();
                $objValidation->setType( 'list');
                $objValidation->setErrorStyle( 'information');
                $objValidation->setAllowBlank(false);
                $objValidation->setShowInputMessage(true);
                $objValidation->setShowErrorMessage(true);
                $objValidation->setShowDropDown(true);
                $objValidation->setErrorTitle('Incorrecto');
                $objValidation->setError('El valor no esta en la lista.');
                $objValidation->setPromptTitle('Elije uno de la lista');
                $objValidation->setPrompt('Por favor elije uno de la lista.');
                $objValidation->setFormula1('=$O$1:$O$'.$index);
                
            }
        });
    }
}
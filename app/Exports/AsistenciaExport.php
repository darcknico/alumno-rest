<?php

namespace App\Exports;
 
use App\Models\Alumno;

use Carbon\Carbon;

use Maatwebsite\Excel\Facades\Excel;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AsistenciaExport implements ShouldAutoSize, FromCollection, WithMapping, WithHeadings
{
    use Exportable;
 
    public function __construct(int $id_asistencia)
    {
        $this->id_asistencia = $id_asistencia;
    }

    public function collection()
    {
        $id_asistencia = $this->id_asistencia;
        $registros = Alumno::rightJoin('tbl_asistencia_alumno','tbl_asistencia_alumno.alu_id','tbl_alumnos.alu_id')
        ->where([
            'tbl_asistencia_alumno.estado' => 1,
            'tbl_asistencia_alumno.asi_id' => $id_asistencia,
        ]);
        return $registros->orderBy('apellido','desc')->get();
    }
 
    public function headings(): array
    {
        return ['Apellido','Nombre','Documento','Asistencia','Observaciones'];
    }

    public function map($registro): array
    {
        return [
            $registro->apellido,
            $registro->nombre,
            $registro->documento,
            '',
            '',
        ];
    }
 
    public function custom(){
        Excel::extend(static::class, function (AsistenciaExport $export, $writer){
            $sheet = $writer->getSheetByIndex(0);
            $sheet->SetCellValue("O1", "Ausente");
            $sheet->SetCellValue("O2", "Presente");
            foreach ($sheet->getColumnIterator('D') as $row) {
                foreach ($row->getCellIterator(2) as $cell) {
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
                    $objValidation->setFormula1('=O1:O2');
                }
                break;
            }
        });
    }
}
<?php

namespace App\Exports;
 
use App\Models\Alumno;

use Carbon\Carbon;

use Maatwebsite\Excel\Facades\Excel;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class MesaExamenMateriaExport implements ShouldAutoSize, FromCollection, WithMapping, WithHeadings,WithColumnFormatting
{
    use Exportable;
 
    public function __construct($id_mesa_examen_materia)
    {
        $this->id_mesa_examen_materia = $id_mesa_examen_materia;
    }

    public function collection()
    {
        $id_mesa_examen_materia = $this->id_mesa_examen_materia;
        $registros = Alumno::rightJoin('tbl_mesa_alumno_materia','tbl_mesa_alumno_materia.alu_id','tbl_alumnos.alu_id')
        ->where([
            'tbl_mesa_alumno_materia.estado' => 1,
            'tbl_mesa_alumno_materia.mma_id' => $id_mesa_examen_materia,
        ]);
        return $registros->orderBy('apellido','desc')->get();
    }
 
    public function headings(): array
    {
        return ['Apellido','Nombre','Documento','Asistencia','Nota','Observaciones'];
    }

    public function map($registro): array
    {
        return [
            $registro->apellido,
            $registro->nombre,
            $registro->documento,
            '',
            0,
            '',
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER,
        ];
    }
 
    public function custom(){
        Excel::extend(static::class, function (MesaExamenMateriaExport $export, $writer){
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
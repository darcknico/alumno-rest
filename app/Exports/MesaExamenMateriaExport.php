<?php

namespace App\Exports;
 
use App\Models\Alumno;
use App\Models\Mesa\MesaExamenMateriaAlumno;
use App\Models\Mesa\MesaExamenMateriaDocente;
use App\Models\Mesa\MesaExamenMateria;

use Maatwebsite\Excel\Facades\Excel;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class MesaExamenMateriaExport implements ShouldAutoSize, FromCollection, WithMapping, WithHeadings, WithColumnFormatting, WithEvents, WithDrawings, WithCustomStartCell
{
    use Exportable;
 
    public function __construct($id_mesa_examen_materia)
    {
        $this->id_mesa_examen_materia = $id_mesa_examen_materia;
        $this->mesa_examen_materia = MesaExamenMateria::find($id_mesa_examen_materia);
    }

    public function collection()
    {
        $registros = MesaExamenMateriaAlumno::where('estado',1)
            ->where('id_mesa_examen_materia',$this->id_mesa_examen_materia);
        return $registros->get()->sortBy('apellido');
    }
 
    public function headings(): array
    {
        return [
            'Apellido',
            'Nombre',
            'Documento',
            'Condicion',
            'Finanzas',
            'Asistencia',
            'Escrito',
            'Oral',
            'Nota Final',
            'Observaciones',
        ];
    }

    public function startCell(): string
    {
        return 'A10';
    }


    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Instituto');
        $drawing->setPath(storage_path('app/images/logo_constancia.png'));
        $drawing->setHeight(50);
        $drawing->setCoordinates('I1');

        return $drawing;
    }

    public function map($registro): array
    {
        $asistencia = '';
        if(is_bool($registro->asistencia)){
            $asistencia = $registro->asistencia?'Presente':'Ausente';
        }
        return [
            $registro->alumno->apellido,
            $registro->alumno->nombre,
            $registro->alumno->documento,
            $registro->condicion->nombre??'',
            $registro->adeuda?'SI':'NO',
            $asistencia,
            '',
            '',
            $registro->nota_final??'',
            $registro->observaciones??'',
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            //'E' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $styleArray = [
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ]
                ];
                $event->sheet->getStyle('A9:J9')->applyFromArray($styleArray);
            },
        ];
    }
 
    public function custom(){
        
        $mesa_examen_materia = $this->mesa_examen_materia;
        Excel::extend(static::class, function (MesaExamenMateriaExport $export, $writer)use($mesa_examen_materia){
            $presidente = MesaExamenMateriaDocente::where('id_tipo_mesa_docente',1)
                ->where('id_mesa_examen_materia',$mesa_examen_materia->id)
                ->first();
            if($presidente){
                $usuario = $presidente->usuario;
                $presidente = $usuario->apellido.', '.$usuario->nombre;
            } else {
                $presidente = "";
            }
            $vocal_1 = MesaExamenMateriaDocente::where('id_tipo_mesa_docente',2)
                ->where('id_mesa_examen_materia',$mesa_examen_materia->id)
                ->first();
            if($vocal_1){
                $usuario = $vocal_1->usuario;
                $vocal_1 = $usuario->apellido.', '.$usuario->nombre;
            } else {
                $vocal_1 = "";
            }
            $vocal_2 = MesaExamenMateriaDocente::where('id_tipo_mesa_docente',3)
                ->where('id_mesa_examen_materia',$mesa_examen_materia->id)
                ->first();
            if($vocal_2){
                $usuario = $vocal_2->usuario;
                $vocal_2 = $usuario->apellido.', '.$usuario->nombre;
            } else {
                $vocal_2 = "";
            }
            $fecha = Carbon::parse($mesa_examen_materia->fecha);
            $sheet = $writer->getSheetByIndex(0);
            $sheet->SetCellValue("A1", "Carrera");
            $sheet->SetCellValue("B1", $mesa_examen_materia->carrera->nombre??"");
            $sheet->SetCellValue("A2", "Materia");
            $sheet->SetCellValue("B2", $mesa_examen_materia->materia->nombre??"");
            $sheet->SetCellValue("D2", "Año");
            $sheet->SetCellValue("E2", $mesa_examen_materia->materia->tipoLectivo->nombre??"");

            $sheet->SetCellValue("A3", "Fecha de Examen");
            $sheet->SetCellValue("B3", $fecha->format('d/m/Y'));
            $sheet->SetCellValue("D3", "Hora de Examen");
            $sheet->SetCellValue("E3", $fecha->format('H:i'));

            $sheet->SetCellValue("A4", "Presidente");
            $sheet->SetCellValue("B4", $presidente);
            $sheet->SetCellValue("D4", "1° Vocal");
            $sheet->SetCellValue("E4", $vocal_1);

            $sheet->SetCellValue("A5", "2° Vocal");
            $sheet->SetCellValue("B5", $vocal_2);

            $sheet->SetCellValue("A6", "Libro");
            $sheet->SetCellValue("B6", $mesa_examen_materia->libro);
            $sheet->SetCellValue("D6", "Folio Libre");
            $sheet->SetCellValue("E6", $mesa_examen_materia->folio_libre);
            $sheet->SetCellValue("F6", "Folio Promocion");
            $sheet->SetCellValue("G6", $mesa_examen_materia->folio_promocion);
            $sheet->SetCellValue("H6", "Folio Regular");
            $sheet->SetCellValue("I6", $mesa_examen_materia->folio_regular);

            $sheet->SetCellValue("A7", "Observaciones");
            $sheet->SetCellValue("B7", $mesa_examen_materia->observaciones);

            $sheet->SetCellValue("A9", "Apellido");
            $sheet->SetCellValue("B9", "Nombre");
            $sheet->SetCellValue("C9", "Documento");
            $sheet->SetCellValue("D9", "Condicion");
            $sheet->SetCellValue("E9", "Finanzas");
            $sheet->SetCellValue("F9", "Asistencia");
            $sheet->SetCellValue("G9", "Escrito");
            $sheet->SetCellValue("H9", "Oral");
            $sheet->SetCellValue("I9", "Nota Final");
            $sheet->SetCellValue("J9", "Observaciones");

        });
        
    }
}
<?php

namespace App\Exports;

use App\Filters\UsuarioFilter;
use App\Usuario;
use App\Models\Beca;
use App\Models\Alumno;
use App\Models\Inscripcion;
use App\Models\PlanPago;
use App\Models\PlanPagoPrecio;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

use App\Functions\CuentaCorrienteFunction;

class BecaExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnFormatting
{
    protected $anio;
	protected $plan_pago_precio;

	public function __construct()
    {
        $this->anio = Carbon::now()->year;
        $this->plan_pago_precio = CuentaCorrienteFunction::ultimo_precio_plan();
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        $registros = Beca::where('estado',1);
        return $registros->orderBy('created_at','desc');
    }

    public function headings(): array
    {
        return [
            'Registrado',
            'Nombre',
            'Descripcion',
            'Descuento a Cuota',
            'Descuento a Matricula',
            'Total Alumnos',
            'Total Inscripciones',
            'Total Alumnos/Corriente',
            'Total Inscripciones/Corriente',
            'Total Bonificado/Cuota/Corriente',
            'Total Bonificado/Matricula/Corriente',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'D' => "#,##0.00%",
            'E' => "#,##0.00%",
            'J' => "$#,##0.00",
            'K' => "$#,##0.00",
        ];
    }

    public function map($registro): array
    {
        $anio = $this->anio;
        $total_alumnos = Alumno::whereHas('inscripciones',function($q)use($registro){
                $q->where('id_beca',$registro->id)
                    ->whereIn('id_tipo_inscripcion_estado',[1,2])
                    ->where('estado',1);
            })
                ->where('estado',1)
                ->count();
        $total_alumnos_corriente = Alumno::whereHas('inscripciones',function($q)use($registro,$anio){
                $q->where('id_beca',$registro->id)
                    ->whereIn('id_tipo_inscripcion_estado',[1,2])
                    ->where('anio',$anio)
                    ->where('estado',1);
            })
            ->where('estado',1)
            ->count();
        $total_inscriptos = Inscripcion::where('id_beca',$registro->id)
            ->whereIn('id_tipo_inscripcion_estado',[1,2])
            ->where('estado',1)
            ->count();
        $total_inscriptos_corriente = Inscripcion::where('id_beca',$registro->id)
            ->whereIn('id_tipo_inscripcion_estado',[1,2])
            ->where('anio',$anio)
            ->where('estado',1)
            ->count();
        $cuotas = PlanPago::whereHas('inscripcion',function($q)use($anio,$registro){
                $q->where('id_beca',$registro->id)
                    ->whereIn('id_tipo_inscripcion_estado',[1,2])
                    ->where('estado',1);
            })
            ->where('anio',$anio)
            ->where('estado',1)
            ->sum('ppa_cuota_cantidad');
        $total_bonificado_cuota_corriente = $cuotas * ($this->plan_pago_precio->cuota_monto - $this->plan_pago_precio->cuota_monto*($registro->porcentaje/100));
        $total_bonificado_matricula_corriente = $total_inscriptos_corriente * ($this->plan_pago_precio->matricula_monto - $this->plan_pago_precio->matricula_monto*($registro->porcentaje_matricula/100));
        return [
            Carbon::parse($registro->created_at)->format('d/m/Y'),
            $registro->nombre,
            $registro->descripcion,
            $registro->porcentaje,
            $registro->porcentaje_matricula,
            $total_alumnos,
            $total_inscriptos,
            $total_alumnos_corriente,
            $total_inscriptos_corriente,
            $total_bonificado_cuota_corriente,
            $total_bonificado_matricula_corriente,
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
                $event->sheet->getStyle('A1:K1')->applyFromArray($styleArray);
            },
        ];
    }
}

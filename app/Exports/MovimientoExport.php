<?php

namespace App\Exports;
 
use App\Models\Alumno;
use App\Models\Movimiento;
use App\Models\Inscripcion;
use App\Models\Carrera;
use App\Models\Pago;

use Carbon\Carbon;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
 
class MovimientoExport implements ShouldAutoSize, FromCollection, WithMapping, WithHeadings, WithColumnFormatting
{
    use Exportable;
 
    public function __construct(
        int $id_sede, $search, 
        int $id_forma_pago,
        int $id_tipo_movimiento,
        int $id_tipo_comprobante,
        int $id_tipo_egreso_ingreso,$fecha_inicio, $fecha_fin)
    {
        $this->id_sede = $id_sede;
        $this->id_forma_pago = $id_forma_pago;
        $this->id_tipo_movimiento = $id_tipo_movimiento;
        $this->id_tipo_comprobante = $id_tipo_comprobante;
        $this->id_tipo_egreso_ingreso = $id_tipo_egreso_ingreso;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
        $this->search = $search;
    }

    public function collection()
    {
        $id_sede = $this->id_sede;
        $id_forma_pago = $this->id_forma_pago;
        $id_tipo_movimiento = $this->id_tipo_movimiento;
        $id_tipo_comprobante = $this->id_tipo_comprobante;
        $id_tipo_egreso_ingreso = $this->id_tipo_egreso_ingreso;
        $fecha_inicio = $this->fecha_inicio;
        $fecha_fin = $this->fecha_fin;
        $search = $this->search;

        $registros = Movimiento::with('forma','usuario')
        ->where([
            'estado' => 1,
            'sed_id' => $id_sede
        ]);
        $registros = $registros
            ->when($id_forma_pago>0,function($q)use($id_forma_pago){
                return $q->where('fpa_id',$id_forma_pago);
            })
            ->when($id_tipo_movimiento>0,function($q)use($id_tipo_movimiento){
                return $q->where('id_tipo_movimiento',$id_tipo_movimiento);
            })
            ->when($id_tipo_comprobante>0,function($q)use($id_tipo_comprobante){
                return $q->where('id_tipo_comprobante',$id_tipo_comprobante);
            })
            ->when($id_tipo_egreso_ingreso>=0,function($q)use($id_tipo_egreso_ingreso){
                return $q->where('tei_id',$id_tipo_egreso_ingreso);
            })
            ->when(!empty($fecha_inicio),function($q)use($fecha_inicio){
                    return $q->whereDate('fecha','>=',$fecha_inicio);
                })
            ->when(!empty($fecha_fin),function($q)use($fecha_fin){
                return $q->whereDate('fecha','<=',$fecha_fin);
            });

        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
              if(strlen($value)>0){
                $registros = $registros->where(function($query) use  ($value) {
                  $query->whereRaw("DATE_FORMAT(mov_fecha, '%d/%m/%Y') like '%".$value."%'");
                });
              }
            }
        }
        return $registros->orderBy('fecha','desc')->get();
    }
 
    public function headings(): array
    {
        return ['Ingreso o Egreso','Fecha','Forma de Pago','Descripcion','Tipo de Movimiento','Monto','Tipo de Comprobante','Numero'];
    }

    public function map($registro): array
    {

        $tipo = "";
        if($registro->id_tipo_egreso_ingreso == 1){
            $tipo = "Ingreso";
        } else {
            $tipo = "Egreso";
        }
        return [
            $tipo,
            Carbon::parse($registro->fecha)->format('d/m/Y'),
            $registro->forma->nombre,
            $registro->descripcion,
            $registro->tipo->nombre??"",
            $registro->monto,
            $registro->tipo_comprobante->nombre??"",
            $registro->numero,
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'F' => "$#,##0.00",
        ];
    }
 
}
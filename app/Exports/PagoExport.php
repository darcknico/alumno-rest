<?php

namespace App\Exports;
 
use App\Models\Alumno;
use App\Models\PlanPago;
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
 
class PagoExport implements ShouldAutoSize, FromCollection, WithMapping, WithHeadings, WithColumnFormatting
{
    use Exportable;
 
    public function __construct(int $id_sede, $search,int $id_tipo_pago, int $id_departamento,int $id_carrera,$fecha_inicio, $fecha_fin)
    {
        $this->id_sede = $id_sede;
        $this->id_tipo_pago = $id_tipo_pago;
        $this->id_departamento = $id_departamento;
        $this->id_carrera = $id_carrera;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
        $this->search = $search;
    }

    public function collection()
    {
        $id_sede = $this->id_sede;
        $id_tipo_pago = $this->id_tipo_pago;
        $id_departamento = $this->id_departamento;
        $id_carrera = $this->id_carrera;
        $fecha_inicio = $this->fecha_inicio;
        $fecha_fin = $this->fecha_fin;
        $search = $this->search;
        $registros = Pago::with([
            'usuario',
            'movimiento.forma',
        ])->where([
            'sed_id' => $id_sede,
            'estado' => 1,
        ]);
        $registros = $registros
            ->when($id_tipo_pago>0,function($q)use($id_tipo_pago){
                return $q->where('id_tipo_pago',$id_tipo_pago);
            })
            ->when($id_departamento>0,function($q)use($id_departamento){
                $carreras = Carrera::where([
                    'dep_id' => $id_departamento,
                    'estado' => 1,
                ])->pluck('car_id')->toArray();
                $inscripciones = Inscripcion::where([
                    'estado' => 1,
                ])
                ->whereIn('car_id',$carreras)
                ->pluck('ins_id')->toArray();
                $planes_pago = PlanPago::whereIn('ins_id',$inscripciones)->where('estado',1)
                ->pluck('ppa_id')->toArray();
                return $q->whereIn('ppa_id',$planes_pago);
            })
            ->when($id_carrera>0,function($q)use($id_carrera){
                $inscripciones = Inscripcion::where([
                    'car_id' => $id_carrera,
                    'estado' => 1,
                ])
                ->pluck('ins_id')->toArray();
                $planes_pago = PlanPago::whereIn('ins_id',$inscripciones)->where('estado',1)
                ->pluck('ppa_id')->toArray();
                return $q->whereIn('ppa_id',$planes_pago);
            })
            ->when(!empty($fecha_inicio),function($q)use($fecha_inicio){
                    return $q->whereDate('pag_fecha','>=',$fecha_inicio);
                })
            ->when(!empty($fecha_fin),function($q)use($fecha_fin){
                return $q->whereDate('pag_fecha','<=',$fecha_fin);
            });
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
                if(strlen($value)>0){
                    $registros = $registros->where(function($query) use  ($value,$id_sede) {
                        $query->where('monto', $value)
                            ->orWhereIn('ppa_id',function($q)use($value,$id_sede){
                                $alumnos = Alumno::where([
                                    'estado' => 1,
                                    'sed_id' => $id_sede,
                                ])
                                ->where('alu_nombre','like','%'.$value.'%')
                                ->orWhere('alu_apellido','like','%'.$value.'%')
                                ->pluck('alu_id')->toArray();
                                $inscripciones = Inscripcion::where([
                                    'estado' => 1,
                                    'sed_id' => $id_sede,
                                ])->whereIn('alu_id',$alumnos)->pluck('ins_id')->toArray();
                                return $q->select('ppa_id')->from('tbl_planes_pago')->where([
                                    'estado' => 1,
                                    'sed_id' => $id_sede,
                                ])->whereIn('ins_id',$inscripciones);
                            });
                    });
                }
            }
        }
        return $registros->orderBy('fecha','desc')->get();
    }
 
    public function headings(): array
    {
        return ['Fecha','Alumno','Carrera','Tipo de Pago','Forma de Pago','Monto'];
    }

    public function map($registro): array
    {
        $plan_pago = PlanPago::find($registro->id_plan_pago);
        $inscripcion = Inscripcion::find($plan_pago->id_inscripcion);
        $alumno = "";
        if($inscripcion){
            $alumno = $inscripcion->alumno->apellido." ".$inscripcion->alumno->nombre;
        }

        $forma = "";
        if($registro->movimiento){
            $forma = $registro->movimiento->forma->nombre;
        } else if($registro->id_tipo_pago == 2 or $registro->id_tipo_pago == 3) {
            $forma = "Bonificacion";
        }
        return [
            Carbon::parse($registro->fecha)->format('d/m/Y'),
            $alumno,
            $inscripcion->carrera->nombre,
            $registro->tipo->nombre,
            $forma,
            $registro->monto,
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
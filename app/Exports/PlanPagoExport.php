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
 
class PlanPagoExport implements ShouldAutoSize, FromCollection, WithMapping, WithHeadings, WithColumnFormatting
{
    use Exportable;
 
    public function __construct(int $id_sede, $search, int $id_departamento,int $id_carrera,$deudores)
    {
        $this->id_sede = $id_sede;
        $this->id_departamento = $id_departamento;
        $this->id_carrera = $id_carrera;
        $this->deudores = $deudores;
        $this->search = $search;
    }

    public function collection()
    {
        $id_sede = $this->id_sede;
        $id_departamento = $this->id_departamento;
        $id_carrera = $this->id_carrera;
        $deudores = $this->deudores;
        $search = $this->search;
        $registros = PlanPago::with([
          'inscripcion.alumno',
          'inscripcion.carrera',
        ])->where([
          'sed_id' => $id_sede,
          'estado' => 1,
        ]);

        $registros = $registros
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
            return $q->whereIn('ins_id',$inscripciones);
          })
          ->when($id_carrera>0,function($q)use($id_carrera){
            $inscripciones = Inscripcion::where([
                'car_id' => $id_carrera,
                'estado' => 1,
              ])
            ->pluck('ins_id')->toArray();
            return $q->whereIn('ins_id',$inscripciones);
          })
          ->when($deudores>0,function($q)use($deudores){
            if($deudores>1){
              return $q->whereNotIn('ppa_id',function($qt){
                return $qt->select('ppa_id')->from('tbl_obligaciones')->where([
                  'estado' => 1,
                ])->where('obl_saldo','>',0);
              });
            } else {
              return $q->whereIn('ppa_id',function($qt){
                return $qt->select('ppa_id')->from('tbl_obligaciones')->where([
                  'estado' => 1,
                ])->where('obl_saldo','>',0);
              });
            }
          });
        $values = explode(" ", $search);
        if(count($values)>0){
          foreach ($values as $key => $value) {
            if(strlen($value)>0){
              $registros = $registros->where(function($query) use  ($value,$id_sede) {
                $query->where('ppa_matricula_monto',$value)
                  ->orWhere('anio',$value)
                  ->orWhere('ppa_cuota_monto',$value)
                  ->orWhereHas('inscripcion',function($q)use($value,$id_sede){
                    $q->whereIn('alu_id',function($qt)use($value,$id_sede){
                        $qt->select('alu_id')->from('tbl_alumnos')
                        ->where('estado',1)
                        ->where('sed_id',$id_sede)
                        ->where(function($qtz) use  ($value){
                            $qtz->where('alu_nombre','like','%'.$value.'%')
                            ->orWhere('alu_apellido','like','%'.$value.'%')
                            ->orWhere('alu_documento',$value);
                        });
                      });
                  });
              });
            }
          }
        }
        return $registros->orderBy('anio','desc')->get();
    }
 
    public function headings(): array
    {
        return ['Fecha','Alumno','Carrera','Año','Total Cuota','Pagado','Saldo Total','Saldo Hoy'];
    }

    public function map($registro): array
    {
        $inscripcion = Inscripcion::find($registro->id_inscripcion);
        $alumno = "";
        if($inscripcion){
            $alumno = $inscripcion->alumno->apellido." ".$inscripcion->alumno->nombre;
        }

        return [
            Carbon::parse($registro->created_at)->format('d/m/Y'),
            $alumno,
            $inscripcion->carrera->nombre,
            $registro->anio,
            $registro->cuota_total,
            $registro->pagado,
            $registro->saldo_total,
            $registro->saldo_hoy,
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'E' => "$#,##0.00",
            'F' => "$#,##0.00",
            'G' => "$#,##0.00",
            'H' => "$#,##0.00",
        ];
    }
 
}
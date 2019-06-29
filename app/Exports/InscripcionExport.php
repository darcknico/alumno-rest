<?php

namespace App\Exports;
 
use App\Models\Alumno;
use App\Models\PlanPago;
use App\Models\Inscripcion;
use App\Models\Carrera;

use Carbon\Carbon;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
 
class InscripcionExport implements ShouldAutoSize, FromCollection, WithMapping, WithHeadings
{
    use Exportable;
 
    public function __construct(
        int $id_sede, 
        $search, 
        int $id_departamento,
        int $id_carrera,
        int $id_beca, 
        int $id_tipo_inscripcion_estado, 
        int $anio_inicial,
        int $anio_final)
    {
        $this->id_sede = $id_sede;
        $this->id_departamento = $id_departamento;
        $this->id_carrera = $id_carrera;
        $this->id_beca = $id_beca;
        $this->id_tipo_inscripcion_estado = $id_tipo_inscripcion_estado;
        $this->anio_inicial = $anio_inicial;
        $this->anio_final = $anio_final;
        $this->search = $search;
    }

    public function collection()
    {
        $id_sede = $this->id_sede;
        $id_departamento = $this->id_departamento;
        $id_carrera = $this->id_carrera;
        $id_beca = $this->id_beca;
        $id_tipo_inscripcion_estado = $this->id_tipo_inscripcion_estado;
        $anio_inicial = $this->anio_inicial;
        $anio_final = $this->anio_final;
        $search = $this->search;
        $registros = Inscripcion::where([
            'sed_id' => $id_sede,
            'estado' => 1,
        ]);
        $registros = $registros
            ->when($id_departamento>0,function($q)use($id_departamento){
                $carreras = Carrera::where([
                    'dep_id' => $id_departamento,
                    'estado' => 1,
                ])->pluck('car_id')->toArray();
                return $q->whereIn('car_id',$carreras);
            })
            ->when($id_carrera>0,function($q)use($id_carrera){
                return $q->where('car_id',$id_carrera);
            })
            ->when($id_beca>0,function($q)use($id_beca){
                return $q->where('bec_id',$id_beca);
            })
            ->when($id_tipo_inscripcion_estado>0,function($q)use($id_tipo_inscripcion_estado){
                return $q->where('tie_id',$id_tipo_inscripcion_estado);
            })
            ->when($anio_inicial>0,function($q)use($anio_inicial){
                return $q->where('anio','>=',$anio_inicial);
            })
            ->when($anio_final>0,function($q)use($anio_final){
                return $q->where('anio','<=',$anio_final);
            });
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
              if(strlen($value)>0){
                $registros = $registros->where(function($query) use  ($value,$id_sede) {
                  $query
                    ->whereIn('car_id',function($q)use($value,$id_sede){
                        $q->select('car_id')->from('tbl_carreras')
                        ->where('sed_id',$id_sede)
                        ->where(function($qt) use  ($value){
                            $qt->where('car_nombre','like','%'.$value.'%')
                            ->orWhere('car_nombre_corto','like','%'.$value.'%');
                        });
                    })
                    ->orWhereIn('alu_id',function($q)use($value,$id_sede){
                        $q->select('alu_id')->from('tbl_alumnos')
                        ->where('sed_id',$id_sede)
                        ->where(function($qt) use  ($value){
                            $qt->where('alu_nombre','like','%'.$value.'%')
                            ->orWhere('alu_apellido','like','%'.$value.'%')
                            ->orWhere('alu_documento',$value);
                        });
                    });
                });
              }
            }
        }
        return $registros->orderBy('car_id','desc')->orderBy('anio','asc')->get();
    }
 
    public function headings(): array
    {
        return ['Documento','Apellido','Nombre','Departamento','Carrera','Plan de Estudio','Beca','Año Inscripcion','Estado','Observaciones'];
    }

    public function map($registro): array
    {
        $beca = "";
        if($registro->beca){
            $beca = $registro->beca->nombre;
        }
        return [
            $registro->alumno->documento,
            $registro->alumno->apellido,
            $registro->alumno->nombre,
            $registro->carrera->departamento->nombre,
            $registro->carrera->nombre,
            $registro->plan_estudio->nombre,
            $beca,
            $registro->anio,
            $registro->tipo_estado->nombre,
            $registro->observaciones,
        ];
    }
 
}
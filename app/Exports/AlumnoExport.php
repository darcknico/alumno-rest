<?php

namespace App\Exports;
 
use App\Models\Alumno;
use App\Models\Inscripcion;
use App\Models\Carrera;

use Carbon\Carbon;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
 
class AlumnoExport implements ShouldAutoSize, FromCollection, WithMapping, WithHeadings
{
    use Exportable;
 
    public function __construct(int $id_sede, $search, int $id_departamento,int $id_carrera,int $id_tipo_alumno_estado)
    {
        $this->id_sede = $id_sede;
        $this->id_departamento = $id_departamento;
        $this->id_carrera = $id_carrera;
        $this->id_tipo_alumno_estado = $id_tipo_alumno_estado;
        $this->search = $search;
    }

    public function collection()
    {
        $id_sede = $this->id_sede;
        $id_departamento = $this->id_departamento;
        $id_carrera = $this->id_carrera;
        $id_tipo_alumno_estado = $this->id_tipo_alumno_estado;
        $search = $this->search;
        $registros = Alumno::where([
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
                ->pluck('alu_id')->toArray();
                return $q->whereIn('alu_id',$inscripciones);
            })
            ->when($id_carrera>0,function($q)use($id_carrera){
                $inscripciones = Inscripcion::where([
                    'car_id' => $id_carrera,
                    'estado' => 1,
                ])->pluck('alu_id')->toArray();
                return $q->whereIn('alu_id',$inscripciones);
            })
            ->when($id_tipo_alumno_estado>0,function($q)use($id_tipo_alumno_estado){
                return $q->where('tae_id',$id_tipo_alumno_estado);
            });
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
                if(strlen($value)>0){
                    $registros = $registros->where(function($query) use  ($value) {
                      $query->where('alu_nombre','like','%'.$value.'%')
                        ->orWhere('alu_apellido','like','%'.$value.'%')
                        ->orWhere('alu_localidad','like','%'.$value.'%')
                        ->orWhere('alu_calle','like','%'.$value.'%')
                        ->orWhere('alu_domicilio','like','%'.$value.'%')
                        ->orWhere('alu_documento','like',$value.'%');
                    });
                }
            }
        }
        return $registros->orderBy('created_at','desc')->get();
    }
 
    public function headings(): array
    {
        return [
            'Tipo',
            'Documento',
            'Apellido',
            'Nombre',
            'Localidad',
            'Codigo Postal',
            'Telefono',
            'Celular',
            'Email',
            'Fecha Nacimiento',
            'Ciudad Nacimiento',
            'Nacionalidad',
            'Sexo',
            'Observaciones'
        ];
    }

    public function map($registro): array
    {
        $fecha_nacimiento = "";
        if($registro->fecha_nacimiento){
            $fecha_nacimiento = Carbon::parse($registro->fecha_nacimiento)->format('d/m/Y');
        }
        $tipo_documento = "";
        if($registro->tipoDocumento){
            $tipo_documento = $registro->tipoDocumento->nombre;
        }
        return [
            $tipo_documento,
            $registro->documento,
            $registro->apellido,
            $registro->nombre,
            $registro->localidad,
            $registro->codigo_postal,
            $registro->telefono,
            $registro->celular,
            $registro->email,
            $fecha_nacimiento,
            $registro->ciudad_nacimiento,
            $registro->nacionalidad,
            $registro->sexo,
            $registro->observaciones,
        ];
    }
 
}
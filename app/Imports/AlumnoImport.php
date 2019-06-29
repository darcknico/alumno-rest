<?php
namespace App\Imports;

use App\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMappedCells;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class AlumnoImport implements WithMappedCells, ToCollection, WithHeadingRow, WithChunkReading
{
	use Importable;

	public function mapping(): array
    {
        return [
            'documento'  => 'A2',
            'apellido' => 'B2',
            'nombre' => 'C2',
            'anio' => 'D2',
            'carrera' => 'E2',
            'beca' => 'F2',
            'matricula' => 'G2',
            'cuota' => 'H2',
            'matricula_pago' => 'I2',
            'cuota_pago_1' => 'J2',
            'cuota_pago_2' => 'K2',
            'cuota_pago_3' => 'L2',
            'cuota_pago_4' => 'M2',
            'cuota_pago_5' => 'N2',
            'cuota_pago_6' => 'O2',
            'cuota_pago_7' => 'P2',
            'cuota_pago_8' => 'Q2',
            'cuota_pago_9' => 'R2',
            'cuota_pago_10' => 'S2',
        ];
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function collection(Collection $rows)
    {
        return $rows;
    }

    public function chunkSize(): int
    {
        return 750;
    }
}
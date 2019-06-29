<?php
namespace App\Imports;

use App\User;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMappedCells;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MesaExamenMateriaImport implements WithMappedCells, WithHeadingRow
{
	use Importable;

	public function mapping(): array
    {
        return [
            'apellido'  => 'A2',
            'nombre' => 'B2',
            'documento' => 'C2',
            'asistencia' => 'D2',
            'nota' => 'E2',
            'observaciones' => 'F2',
        ];
    }

    public function headingRow(): int
    {
        return 1;
    }
    
}
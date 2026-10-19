<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClientesTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'identificacion',
            'nombres',
            'apellidos',
            'telefono',
            'email',
            'direccion',
            'pais',
            'ciudad',
            'estado',
            'codigo_postal',
            'apartamento',
            'edad',
            'deuda',
            'abonado',
            'oficina'
        ];
    }

    public function array(): array
    {
        return [
            [
                '0923456789',
                'Juan Carlos',
                'Pérez Gómez',
                '0991234567',
                'juan.perez@example.com',
                'Av. 9 de Octubre y Malecón',
                'Ecuador',
                'Guayaquil',
                'Guayas',
                '090150',
                'Piso 3 Depto 302',
                '35',
                '0.00',
                '0.00',
                'Oficina Central'
            ],
            [
                '1712345678',
                'María Elena',
                'López Castro',
                '0987654321',
                'maria.lopez@example.com',
                'Av. Amazonas y Naciones Unidas',
                'Ecuador',
                'Quito',
                'Pichincha',
                '170515',
                'Oficina 4B',
                '42',
                '150.00',
                '50.00',
                'Sucursal Norte'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4F46E5'] // Indigo 600
                ]
            ],
        ];
    }
}

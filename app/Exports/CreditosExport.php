<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CreditosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private Builder $builder) {}

    public function collection()
    {
        return $this->builder->orderBy('id')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Cliente',
            'Tipo crédito',
            'Monto (Q)',
            'Plazo (meses)',
            'Asesor',
            'Etapa actual',
            'Fecha concesión',
            'Fecha vencimiento',
        ];
    }

    public function map($c): array
    {
        return [
            $c->id,
            $c->cliente?->nombre_cliente,
            $c->tipoCredito?->nombre,
            (float) $c->monto,
            (int) $c->plazo,
            $c->asesor?->name,
            $c->ultimoEstado?->estado?->nombre ?? '',
            optional($c->fecha_concesion)->format('Y-m-d'),
            optional($c->fecha_vencimiento)->format('Y-m-d'),
        ];
    }
}

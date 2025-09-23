<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstadoCredito;

class EstadosCreditoSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['orden' => 1,  'nombre' => 'Prospección'],
            ['orden' => 2,  'nombre' => 'Conformación de expediente'],
            ['orden' => 3,  'nombre' => 'En análisis'],
            ['orden' => 4,  'nombre' => 'En requerimiento de análisis'],
            ['orden' => 5,  'nombre' => 'Asignado a comité/junta'],
            ['orden' => 6,  'nombre' => 'Aprobado/en elaboración de minuta'],
            ['orden' => 7,  'nombre' => 'Caso ingresado al registro de la propiedad'],
            ['orden' => 8,  'nombre' => 'En jurídico/visado'],
            ['orden' => 9,  'nombre' => 'Expediente asignado a revisión de cartera'],
            ['orden' => 10, 'nombre' => 'Desembolsado'],
        ];

        foreach ($stages as $s) {
            EstadoCredito::updateOrCreate(
                ['nombre' => $s['nombre']],
                ['orden'  => $s['orden']]
            );
        }
    }
}

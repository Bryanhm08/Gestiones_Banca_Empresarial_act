<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadosCreditoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('estados_credito')->insert([
            ['nombre' => 'Sin estado'],
            ['nombre' => 'Solicitud'],
            ['nombre' => 'Análisis'],
            ['nombre' => 'Comité'],
            ['nombre' => 'Formalización'],
            ['nombre' => 'Asignado desembolso'],
            ['nombre' => 'Desembolsado'],
            ['nombre' => 'Desistido'],
        ]);

    }
}

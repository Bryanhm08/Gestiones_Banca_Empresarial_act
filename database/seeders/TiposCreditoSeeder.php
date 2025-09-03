<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposCreditoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tipos_credito')->insert([
            ['nombre' => 'Crédito corriente'],
            ['nombre' => 'Decreciente'],
            ['nombre' => 'Línea de crédito'],
            ['nombre' => 'Transitorio'],
        ]);
    }
}

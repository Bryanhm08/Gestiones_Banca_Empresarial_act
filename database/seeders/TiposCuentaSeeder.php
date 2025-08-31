<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposCuentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tipos_cuenta')->insert([
            ['nombre' => 'Cuenta Monetaria'],
            ['nombre' => 'Ahorro'],
            ['nombre' => 'Ahorro Preferente'],
            ['nombre' => 'Plazo Fijo'],
        ]);

    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class AreaModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('area_has_modules')->insert([
            ['area_id' => 2, 'modulo' => 'Reportes de créditos'],
            ['area_id' => 3, 'modulo' => 'Reportería de cuentas'],
            ['area_id' => 4, 'modulo' => 'Reportes de créditos'],
            ['area_id' => 4, 'modulo' => 'Reportes de cuentas'],
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('areas')->insert([
            ['nombre' => 'Banca Empresarial'],
            ['nombre' => 'Cobros'],
            ['nombre' => 'Agencias'],
            ['nombre' => 'Auditoria'],
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GarantiasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('garantias')->insert([
            ['nombre' => 'Hipotecaria'],
            ['nombre' => 'Fiduciaria'],
            ['nombre' => 'Mobiliaria'],
            ['nombre' => 'Mixta'],
        ]);

    }
}

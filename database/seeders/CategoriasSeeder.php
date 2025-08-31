<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class CategoriasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categorias')->insert([
            ['nombre' => 'Persona individual'],
            ['nombre' => 'Empresa individual'],
            ['nombre' => 'Sociedad Anónima'],
            ['nombre' => 'Cooperativa'],
            ['nombre' => 'Sociedad Colectiva'],
            ['nombre' => 'Sociedad en Comandita Simple'],
            ['nombre' => 'Sociedad en Comandita por Acciones'],
            ['nombre' => 'Sociedad de Responsabilidad Limitada'],
        ]);
    }
}

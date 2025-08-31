<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AreasSeeder::class,
            CategoriasSeeder::class,
            TiposCreditoSeeder::class,
            GarantiasSeeder::class,
            EstadosCreditoSeeder::class,
            TiposCuentaSeeder::class,
            AreaModulesSeeder::class,
            AdminUsersSeeder::class,
        ]);
    }
}

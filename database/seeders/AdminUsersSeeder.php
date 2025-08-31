<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUsersSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $areaAuditoria = DB::table('areas')->where('nombre', 'Auditoria')->value('id');
        $areaCobros = DB::table('areas')->where('nombre', 'Cobros')->value('id');

        $users = [
            [
                'username' => 'bnlopez',
                'name' => 'Brandon Neftalí López Estrada',
                'area_id' => $areaAuditoria,
                'puesto' => 'Administrador de Sistema',
                'asesor' => 0,
                'admin' => 1,
                'email' => 'bnlopez@mintrabajo.gob.gt',
                'email_verified_at' => $now,
                'password' => Hash::make('Brandon12'),
                'estado' => 1,
                'remember_token' => Str::random(10),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username' => 'bryanth',
                'name' => 'Bryan Steven Thadeus Hernández Moran',
                'area_id' => $areaCobros,
                'puesto' => 'Administrador de Sistema',
                'asesor' => 0,
                'admin' => 1,
                'email' => 'bryanhm08@gmail.com',
                'email_verified_at' => $now,
                'password' => Hash::make('Bryan12'),
                'estado' => 1,
                'remember_token' => Str::random(10),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('users')->upsert(
            $users,
            ['email', 'username'],
            ['name', 'area_id', 'puesto', 'asesor', 'admin', 'password', 'estado', 'updated_at']
        );
    }
}

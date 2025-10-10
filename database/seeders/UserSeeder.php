<?php

namespace Database\Seeders;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear un Administrador (ADMIN)
        DB::table('users')->updateOrInsert(
            ['email' => 'renealdm3@gmail.com'], // Criterio de búsqueda (clave única)
            [
                'name' => 'Alfonso Rene',
                'apellido_paterno' => 'Aldama',
                'apellido_materno' => 'Trinidad',
                'rol' => 'COORDINADOR', // Rol clave
                'password' => Hash::make('Alfonso33'), // ¡IMPORTANTE! Contraseña encriptada
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        // 2. Crear un Maestro de prueba (MAESTRO)
        DB::table('users')->updateOrInsert(
            ['email' => 'maestro@sistema.com'],
            [
                'name' => 'Juanito',
                'apellido_paterno' => 'Perez',
                'apellido_materno' => 'Sinaloa',
                'rol' => 'MAESTRO',
                'password' => Hash::make('Juanito33'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'noel02_2003@hotmail.com'],
            [
                'name' => 'Pavel Noel',
                'apellido_paterno' => 'Dominguez',
                'apellido_materno' => 'Reyes',
                'rol' => 'DIRECTOR',
                'password' => Hash::make('password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}

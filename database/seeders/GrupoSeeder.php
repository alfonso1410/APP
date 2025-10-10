<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $grupos = [
            // --- Grupos de Primer Grado (grado_id: 4) ---
            ['grado_id' => 4, 'nombre' => 'A', 'ciclo_escolar' => '2025-2026', 'tipo_grupo' => 'REGULAR', 'estado' => 'ACTIVO'], // ID Asignado: 1
            
            // --- Grupos de Segundo Grado (grado_id: 5) ---
            ['grado_id' => 5, 'nombre' => 'A', 'ciclo_escolar' => '2025-2026', 'tipo_grupo' => 'REGULAR', 'estado' => 'ACTIVO'], // ID Asignado: 2

            // --- Grupos de Tercer Grado (grado_id: 6) ---
            ['grado_id' => 6, 'nombre' => 'A', 'ciclo_escolar' => '2025-2026', 'tipo_grupo' => 'REGULAR', 'estado' => 'ACTIVO'], // ID Asignado: 3

            // --- Grupos Extracurriculares ---
            ['grado_id' => 4, 'nombre' => 'Ajedrez', 'ciclo_escolar' => '2025-2026', 'tipo_grupo' => 'EXTRA', 'estado' => 'ACTIVO'],   // ID Asignado: 4
            ['grado_id' => 5, 'nombre' => 'Fútbol', 'ciclo_escolar' => '2025-2026', 'tipo_grupo' => 'EXTRA', 'estado' => 'ACTIVO'],    // ID Asignado: 5
        ];

        foreach ($grupos as $grupo) {
            // Usamos updateOrInsert para evitar duplicados si se ejecuta el seeder varias veces
            DB::table('grupos')->updateOrInsert(
                [
                    'grado_id'      => $grupo['grado_id'],
                    'nombre'        => $grupo['nombre'],
                    'ciclo_escolar' => $grupo['ciclo_escolar']
                ],
                $grupo // Datos para insertar o actualizar
            );
        }
    }
}
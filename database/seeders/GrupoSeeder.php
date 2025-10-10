<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class GrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $grupos = [
            // === GRUPO NUEVO DE PREESCOLAR (grado_id: 1) ===
            ['grado_id' => 1, 'nombre_grupo' => 'A', 'ciclo_escolar' => '2025-2026', 'tipo_grupo' => 'REGULAR', 'estado' => 'ACTIVO'], // ID Asignado: 1

            // --- Grupos de Primaria ---
            ['grado_id' => 4, 'nombre_grupo' => 'A', 'ciclo_escolar' => '2025-2026', 'tipo_grupo' => 'REGULAR', 'estado' => 'ACTIVO'], // ID Asignado: 2
            ['grado_id' => 5, 'nombre_grupo' => 'A', 'ciclo_escolar' => '2025-2026', 'tipo_grupo' => 'REGULAR', 'estado' => 'ACTIVO'], // ID Asignado: 3
            ['grado_id' => 6, 'nombre_grupo' => 'A', 'ciclo_escolar' => '2025-2026', 'tipo_grupo' => 'REGULAR', 'estado' => 'ACTIVO'], // ID Asignado: 4

            // --- Grupos Extracurriculares ---
            ['grado_id' => 4, 'nombre_grupo' => 'Ajedrez', 'ciclo_escolar' => '2025-2026', 'tipo_grupo' => 'EXTRA', 'estado' => 'ACTIVO'],   // ID Asignado: 5
            ['grado_id' => 5, 'nombre_grupo' => 'Fútbol', 'ciclo_escolar' => '2025-2026', 'tipo_grupo' => 'EXTRA', 'estado' => 'ACTIVO'],    // ID Asignado: 6
        ];

        foreach ($grupos as $grupo) {
            // Buscamos por la clave compuesta para idempotencia
            DB::table('grupos')->updateOrInsert(
                [
                    'nombre_grupo' => $grupo['nombre_grupo'],
                    'grado_id' => $grupo['grado_id'],
                    'ciclo_escolar' => $grupo['ciclo_escolar'],
                ],
                [
                    'tipo_grupo' => $grupo['tipo_grupo'],
                    'estado' => $grupo['estado'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
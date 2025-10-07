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
            // Grupo de Primero de Primaria (asumiendo grado_id 4)
            [
                'grado_id' => 4, 
                'nombre_grupo' => 'A',
                'ciclo_escolar' => '2025-2026',
                'tipo_grupo' => 'REGULAR',
                'estado' => 'ACTIVO',
            ],
            // Grupo de Segundo de Primaria (asumiendo grado_id 5)
            [
                'grado_id' => 5, 
                'nombre_grupo' => 'B',
                'ciclo_escolar' => '2025-2026',
                'tipo_grupo' => 'REGULAR',
                'estado' => 'ACTIVO',
            ],
            // Grupo Extracurricular (asumiendo grado_id 5, por ejemplo, pero puede ser cualquiera)
            [
                'grado_id' => 5, 
                'nombre_grupo' => 'Ajedrez',
                'ciclo_escolar' => '2025-2026',
                'tipo_grupo' => 'EXTRA',
                'estado' => 'ACTIVO',
            ],
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

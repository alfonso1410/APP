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
              // --- Grupos de Tercer Grado (grado_id: 6) ---
            ['grado_id' => 6, 'nombre_grupo' => 'A', 'ciclo_escolar' => '2025-2026', 'tipo_grupo' => 'REGULAR', 'estado' => 'ACTIVO'], // ID Asignado: 3
            //extracurricular
            ['grado_id' => 5, 'nombre_grupo' => 'Fútbol', 'ciclo_escolar' => '2025-2026', 'tipo_grupo' => 'EXTRA', 'estado' => 'ACTIVO'], 
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
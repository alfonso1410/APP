<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class GrupoMateriaMaestroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asignación de prueba: Materia Español (ID 1) en Grupo A (ID 1) al Maestro (ID 2)
        $asignaciones = [
            [
                'grupo_id' => 1, 
                'materia_id' => 1, // Español
                'maestro_id' => 2, // maestro@sistema.com
            ],
            // Asignación de prueba: Materia Matemáticas (ID 2) en Grupo A (ID 1) al mismo Maestro (ID 2)
            [
                'grupo_id' => 1, 
                'materia_id' => 2, // Matemáticas
                'maestro_id' => 2, 
            ],
        ];

        foreach ($asignaciones as $asignacion) {
            DB::table('grupo_materia_maestro')->updateOrInsert(
                // Clave de Búsqueda (grupo_id, materia_id)
                [
                    'grupo_id' => $asignacion['grupo_id'],
                    'materia_id' => $asignacion['materia_id'],
                ],
                // Valores a Insertar/Actualizar
                [
                    'maestro_id' => $asignacion['maestro_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class EstructuraCurricularSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $estructura = [
            // GRADO 4 (Primero de Primaria)
            // CAMPO 1 (Lenguajes)
            ['grado_id' => 4, 'campo_id' => 1, 'materia_id' => 1, 'campo_ponderacion' => 0.60], // Materia 1: Español (60%)
            ['grado_id' => 4, 'campo_id' => 1, 'materia_id' => 7, 'campo_ponderacion' => 0.40], // Materia 7: Artística (40%)
            // CAMPO 2 (Saberes y Pensamiento Científico)
            ['grado_id' => 4, 'campo_id' => 2, 'materia_id' => 2, 'campo_ponderacion' => 0.50], // Materia 2: Matemáticas (50%)
            ['grado_id' => 4, 'campo_id' => 2, 'materia_id' => 3, 'campo_ponderacion' => 0.50], // Materia 3: Ciencias Naturales (50%)
            
            // GRADO 5 (Segundo de Primaria)
            // CAMPO 1 (Lenguajes)
            ['grado_id' => 5, 'campo_id' => 1, 'materia_id' => 1, 'campo_ponderacion' => 1.0], // Español, única materia en el campo
        ];

        foreach ($estructura as $item) {
            DB::table('estructura_curricular')->updateOrInsert(
                // Clave de Búsqueda (Primary Key Compuesta)
                [
                    'grado_id' => $item['grado_id'],
                    'campo_id' => $item['campo_id'],
                    'materia_id' => $item['materia_id'],
                ],
                // Valores a Insertar/Actualizar
                [
                    'campo_ponderacion' => $item['campo_ponderacion'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

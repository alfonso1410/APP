<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class MateriaCriteriosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        // Se definen criterios para la Materia 1 (Español)
        $criterios = [
            // Materia_ID 1 (Español)
            // Criterio 1: Examen Escrito
            ['materia_id' => 1, 'catalogo_criterio_id' => 1, 'ponderacion' => 0.50, 'incluido_en_promedio' => true],
            // Criterio 2: Tareas / Trabajos en Casa
            ['materia_id' => 1, 'catalogo_criterio_id' => 2, 'ponderacion' => 0.30, 'incluido_en_promedio' => true],
            // Criterio 3: Participación en Clase
            ['materia_id' => 1, 'catalogo_criterio_id' => 3, 'ponderacion' => 0.20, 'incluido_en_promedio' => true],
        ];

        foreach ($criterios as $item) {
            DB::table('materia_criterios')->updateOrInsert(
                [
                    'materia_id' => $item['materia_id'],
                    'catalogo_criterio_id' => $item['catalogo_criterio_id']
                ],
                [
                    'ponderacion' => $item['ponderacion'],
                    'incluido_en_promedio' => $item['incluido_en_promedio'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

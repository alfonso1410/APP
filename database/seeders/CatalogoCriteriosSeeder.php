<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CatalogoCriteriosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $criterios = [
            ['catalogo_criterio_id' => 1, 'nombre' => 'Examen Escrito'],
            ['catalogo_criterio_id' => 2, 'nombre' => 'Tareas / Trabajos en Casa'],
            ['catalogo_criterio_id' => 3, 'nombre' => 'Participación en Clase'],
            ['catalogo_criterio_id' => 4, 'nombre' => 'Proyecto Integrador'],
            ['catalogo_criterio_id' => 5, 'nombre' => 'Exposición Oral'],
            ['catalogo_criterio_id' => 6, 'nombre' => 'Asistencia y Puntualidad'],
        ];

        // Insertamos los datos usando updateOrInsert para idempotencia
        foreach ($criterios as $criterio) {
            DB::table('catalogo_criterios')->updateOrInsert(
                ['catalogo_criterio_id' => $criterio['catalogo_criterio_id']],
                [
                    'nombre' => $criterio['nombre'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
    }
    }
}

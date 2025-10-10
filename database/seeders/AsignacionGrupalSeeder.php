<?php

namespace Database\Seeders;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class AsignacionGrupalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       // Alumnos de prueba (ID 1 y 2)
        $asignaciones = [
            // Alumno 1 en Grupo Regular 1
            ['alumno_id' => 1, 'grupo_id' => 1, 'es_actual' => true],
            // Alumno 2 en Grupo Regular 1
            ['alumno_id' => 2, 'grupo_id' => 1, 'es_actual' => true],
            ['alumno_id' => 10, 'grupo_id' => 1, 'es_actual' => true],// Diego

             // --- Alumnos de Segundo "A" (grupo_id: 2) ---
            ['alumno_id' => 4, 'grupo_id' => 2, 'es_actual' => true], // Mateo
            ['alumno_id' => 5, 'grupo_id' => 2, 'es_actual' => true], // Valentina

            // --- Alumnos de Tercero "A" (grupo_id: 3) ---
            ['alumno_id' => 6, 'grupo_id' => 3, 'es_actual' => true], // Leonardo
            ['alumno_id' => 7, 'grupo_id' => 3, 'es_actual' => true], // Isabella
            ['alumno_id' => 8, 'grupo_id' => 3, 'es_actual' => true], // Santiago
            ['alumno_id' => 9, 'grupo_id' => 3, 'es_actual' => true], // Camila

            // Alumno 1 también toma el grupo Extra 3 (Ajedrez)
            ['alumno_id' => 1, 'grupo_id' => 3, 'es_actual' => true],
            ['alumno_id' => 10, 'grupo_id' => 4, 'es_actual' => true], // Diego (1ro)
            ['alumno_id' => 5, 'grupo_id' => 4, 'es_actual' => true],  // Valentina (2do)

            // --- Fútbol (grupo_id: 5) ---
            ['alumno_id' => 4, 'grupo_id' => 5, 'es_actual' => true],  // Mateo (2do)
            ['alumno_id' => 6, 'grupo_id' => 5, 'es_actual' => true],  // Leonardo (3ro)
            ['alumno_id' => 9, 'grupo_id' => 5, 'es_actual' => true],  // Camila (3ro)
        ];

        foreach ($asignaciones as $asignacion) {
            DB::table('asignacion_grupal')->updateOrInsert(
                // Clave de Búsqueda (alumno_id, grupo_id)
                [
                    'alumno_id' => $asignacion['alumno_id'],
                    'grupo_id' => $asignacion['grupo_id'],
                ],
                // Valores a Insertar/Actualizar
                [
                    'es_actual' => $asignacion['es_actual'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

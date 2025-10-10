<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsignacionGrupalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // 1. Limpiamos la tabla para un inicio limpio.
        DB::table('asignacion_grupal')->delete();

        $asignaciones = [
            // === ALUMNOS DE PREESCOLAR "A" (grupo_id: 1) ===
            ['alumno_id' => 1, 'grupo_id' => 1, 'es_actual' => true], // Sofía
            ['alumno_id' => 2, 'grupo_id' => 1, 'es_actual' => true], // Ricardo
            ['alumno_id' => 10, 'grupo_id' => 1, 'es_actual' => true],// Diego

            // --- Alumnos de Primero "A" (grupo_id: 2) ---
            ['alumno_id' => 4, 'grupo_id' => 2, 'es_actual' => true], // Mateo
            ['alumno_id' => 5, 'grupo_id' => 2, 'es_actual' => true], // Valentina

            // --- Alumnos de Segundo "A" (grupo_id: 3) ---
            ['alumno_id' => 6, 'grupo_id' => 3, 'es_actual' => true], // Leonardo
            ['alumno_id' => 7, 'grupo_id' => 3, 'es_actual' => true], // Isabella

            // --- Alumnos de Tercero "A" (grupo_id: 4) ---
            ['alumno_id' => 8, 'grupo_id' => 4, 'es_actual' => true], // Santiago
            ['alumno_id' => 9, 'grupo_id' => 4, 'es_actual' => true], // Camila

            // === Asignaciones Extracurriculares ===

            // --- Ajedrez (grupo_id: 5) ---
            ['alumno_id' => 4, 'grupo_id' => 5, 'es_actual' => true], // Mateo (1ro)
            ['alumno_id' => 5, 'grupo_id' => 5, 'es_actual' => true], // Valentina (1ro)

            // --- Fútbol (grupo_id: 6) ---
            ['alumno_id' => 6, 'grupo_id' => 6, 'es_actual' => true], // Leonardo (2do)
            ['alumno_id' => 9, 'grupo_id' => 6, 'es_actual' => true], // Camila (3ro)
        ];
        
        // Nota: El alumno 3 (Emilia) está INACTIVO, por lo que no se le asigna ningún grupo.
        
        // 2. Insertamos todas las asignaciones con una sola consulta. Es más eficiente.
        DB::table('asignacion_grupal')->insert($asignaciones);
    }
}
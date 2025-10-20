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
        // IDs basados en el orden de creación de tus otros seeders.
        $asignaciones = [
            
            // --- ASIGNACIONES REGULARES (Cada alumno tiene uno) ---

            // Alumnos en 'Primero Preescolar - A' (grupo_id: 1)
            ['alumno_id' => 1, 'grupo_id' => 1],
            ['alumno_id' => 2, 'grupo_id' => 1],

            // Alumnos en 'Primero de Primaria - A' (grupo_id: 3)
            ['alumno_id' => 3, 'grupo_id' => 3],
            ['alumno_id' => 4, 'grupo_id' => 3],

            // Alumnos en 'Primero de Primaria - B' (grupo_id: 4)
            ['alumno_id' => 5, 'grupo_id' => 4],
            ['alumno_id' => 6, 'grupo_id' => 4],

            // Alumnos en 'Segundo de Primaria - A' (grupo_id: 5)
            ['alumno_id' => 7, 'grupo_id' => 5],
            ['alumno_id' => 8, 'grupo_id' => 5],
            ['alumno_id' => 9, 'grupo_id' => 5],
            ['alumno_id' => 10, 'grupo_id' => 5],

            
            // --- ASIGNACIONES EXTRA (Solo 5 alumnos, respetando el mapeo) ---

            // Alumno 2 (de Preescolar) es compatible con 'Yoga Preescolar' (grupo_id: 6)
            ['alumno_id' => 2, 'grupo_id' => 6],
            
            // Alumno 3 (de 1° Primaria) es compatible con 'Yoga (1° y 2°)' - Grupo A (grupo_id: 7)
            ['alumno_id' => 3, 'grupo_id' => 7],

            // Alumno 5 (de 1° Primaria) es compatible con 'Yoga (1° y 2°)' - Grupo B (grupo_id: 8)
            ['alumno_id' => 5, 'grupo_id' => 8],

            // Alumno 7 (de 2° Primaria) es compatible con 'Yoga (1° y 2°)' - Grupo A (grupo_id: 7)
            ['alumno_id' => 7, 'grupo_id' => 7],

            // Alumno 9 (de 2° Primaria) es compatible con 'Yoga (1° y 2°)' - Grupo B (grupo_id: 8)
            ['alumno_id' => 9, 'grupo_id' => 8],
        ];

        // Preparamos los datos para una única inserción
        $dataToInsert = array_map(function ($asignacion) {
            $asignacion['es_actual'] = true;
            $asignacion['created_at'] = now();
            $asignacion['updated_at'] = now();
            return $asignacion;
        }, $asignaciones);

        // Insertamos todos los registros en una sola consulta
        DB::table('asignacion_grupal')->insert($dataToInsert);
    }
}
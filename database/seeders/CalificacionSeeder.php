<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CalificacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $calificaciones = [
            // Calificaciones del Alumno 1 en Periodo 1 para la Materia: Español
            // Materia_Criterio_ID 1: Examen Escrito (50% ponderación)
            [
                'alumno_id' => 1, 
                'materia_criterio_id' => 1, 
                'periodo_id' => 1, 
                'calificacion_obtenida' => 9.5
            ],
            // Materia_Criterio_ID 2: Tareas (30% ponderación)
            [
                'alumno_id' => 1, 
                'materia_criterio_id' => 2, 
                'periodo_id' => 1, 
                'calificacion_obtenida' => 8.0
            ],
            // Materia_Criterio_ID 3: Participación (20% ponderación)
            [
                'alumno_id' => 1, 
                'materia_criterio_id' => 3, 
                'periodo_id' => 1, 
                'calificacion_obtenida' => 10.0
            ],
        ];

        foreach ($calificaciones as $calificacion) {
            DB::table('calificaciones')->updateOrInsert(
                [
                    'alumno_id' => $calificacion['alumno_id'],
                    'materia_criterio_id' => $calificacion['materia_criterio_id'],
                    'periodo_id' => $calificacion['periodo_id'],
                ],
                [
                    'calificacion_obtenida' => $calificacion['calificacion_obtenida'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

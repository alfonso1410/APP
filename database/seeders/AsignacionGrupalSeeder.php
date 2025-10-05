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
            
            // Alumno 1 también toma el grupo Extra 3 (Ajedrez)
            ['alumno_id' => 1, 'grupo_id' => 3, 'es_actual' => true],
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

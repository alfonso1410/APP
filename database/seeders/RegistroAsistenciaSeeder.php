<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
// Asegúrate de que esta línea esté si usas DB::table

class RegistroAsistenciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fecha_asistencia = '2025-09-02';
        
        // --- CORRECCIÓN 1: Renombrar 'turno' a 'idioma' y cambiar valores ---
        $registros = [
            // Alumno 1 en Grupo 1: Asistencia de ESPAÑOL
            [
                'alumno_id' => 1, 
                'grupo_id' => 1, 
                'fecha' => $fecha_asistencia, 
                'tipo_asistencia' => 'PRESENTE',
                'idioma' => 'ESPAÑOL' // <-- CORREGIDO
            ],
            // Alumno 2 en Grupo 1: Asistencia de ESPAÑOL
            [
                'alumno_id' => 2, 
                'grupo_id' => 1, 
                'fecha' => $fecha_asistencia, 
                'tipo_asistencia' => 'FALTA',
                'idioma' => 'ESPAÑOL' // <-- CORREGIDO
            ],
            // Alumno 1 en Grupo 1: Segunda asistencia del día (INGLES)
            [
                'alumno_id' => 1, 
                'grupo_id' => 1, 
                'fecha' => $fecha_asistencia, 
                'tipo_asistencia' => 'PRESENTE',
                'idioma' => 'INGLES' // <-- CORREGIDO
            ],
        ];

        foreach ($registros as $registro) {
            DB::table('registro_asistencia')->updateOrInsert(
                // --- CORRECCIÓN 2: Clave de Búsqueda COMPLETA ahora usa 'idioma' ---
                [
                    'alumno_id' => $registro['alumno_id'],
                    'grupo_id' => $registro['grupo_id'],
                    'fecha' => $registro['fecha'],
                    'idioma' => $registro['idioma'], // <-- CORREGIDO
                ],
                // Valores a Insertar/Actualizar
                [
                    'tipo_asistencia' => $registro['tipo_asistencia'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
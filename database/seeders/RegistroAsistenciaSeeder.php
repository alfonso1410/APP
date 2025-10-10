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
        
        $registros = [
            // Alumno 1 en Grupo 1: Asistencia de INICIO_DIA (Maestro 1)
            [
                'alumno_id' => 1, 
                'grupo_id' => 1, 
                'fecha' => $fecha_asistencia, 
                'tipo_asistencia' => 'PRESENTE',
                'turno' => 'INICIO_DIA' // Campo nuevo añadido
            ],
            // Alumno 2 en Grupo 1: Asistencia de INICIO_DIA (Maestro 1)
            [
                'alumno_id' => 2, 
                'grupo_id' => 1, 
                'fecha' => $fecha_asistencia, 
                'tipo_asistencia' => 'FALTA',
                'turno' => 'INICIO_DIA' // Campo nuevo añadido
            ],
            // Alumno 1 en Grupo 1: Segunda asistencia a mitad del día (Maestro 2)
            [
                'alumno_id' => 1, 
                'grupo_id' => 1, 
                'fecha' => $fecha_asistencia, 
                'tipo_asistencia' => 'PRESENTE',
                'turno' => 'MEDIO_DIA' // Demuestra que puede haber 2 registros por alumno por día
            ],
        ];

        foreach ($registros as $registro) {
            DB::table('registro_asistencia')->updateOrInsert(
                // Clave de Búsqueda COMPLETA (incluye el nuevo campo 'turno')
                [
                    'alumno_id' => $registro['alumno_id'],
                    'grupo_id' => $registro['grupo_id'],
                    'fecha' => $registro['fecha'],
                    'turno' => $registro['turno'], // <-- CAMBIO CLAVE
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
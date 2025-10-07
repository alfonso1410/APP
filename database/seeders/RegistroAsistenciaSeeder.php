<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class RegistroAsistenciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $fecha_asistencia = '2025-09-02';
        
        $registros = [
            // Alumno 1 en Grupo 1: Presente
            [
                'alumno_id' => 1, 
                'grupo_id' => 1, 
                'fecha' => $fecha_asistencia, 
                'tipo_asistencia' => 'PRESENTE'
            ],
            // Alumno 2 en Grupo 1: Falta
            [
                'alumno_id' => 2, 
                'grupo_id' => 1, 
                'fecha' => $fecha_asistencia, 
                'tipo_asistencia' => 'FALTA'
            ],
        ];

        foreach ($registros as $registro) {
            DB::table('registro_asistencia')->updateOrInsert(
                // Clave de Búsqueda (alumno_id, grupo_id, fecha)
                [
                    'alumno_id' => $registro['alumno_id'],
                    'grupo_id' => $registro['grupo_id'],
                    'fecha' => $registro['fecha'],
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

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class GrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // IDs de los grados se asumen en orden de creación del GradoSeeder
        $grupos = [
            // --- GRUPOS REGULARES ---
            ['grado_id' => 1, 'nombre_grupo' => 'A', 'tipo_grupo' => 'REGULAR'], // Para 'Primero Preescolar'
            ['grado_id' => 2, 'nombre_grupo' => 'A', 'tipo_grupo' => 'REGULAR'], // Para 'Segundo Preescolar'
            ['grado_id' => 4, 'nombre_grupo' => 'A', 'tipo_grupo' => 'REGULAR'], // Para 'Primero' de Primaria
            ['grado_id' => 4, 'nombre_grupo' => 'B', 'tipo_grupo' => 'REGULAR'], // Para 'Primero' de Primaria
            ['grado_id' => 5, 'nombre_grupo' => 'A', 'tipo_grupo' => 'REGULAR'], // Para 'Segundo' de Primaria

            // --- GRUPOS EXTRACURRICULARES ---
            ['grado_id' => 10, 'nombre_grupo' => 'Único', 'tipo_grupo' => 'EXTRA'],// Para 'Yoga Preescolar'
            ['grado_id' => 12, 'nombre_grupo' => 'A', 'tipo_grupo' => 'EXTRA'],    // Para 'Yoga (1° y 2°)'
            ['grado_id' => 12, 'nombre_grupo' => 'B', 'tipo_grupo' => 'EXTRA'],    // Para 'Yoga (1° y 2°)'
            ['grado_id' => 14, 'nombre_grupo' => 'Único', 'tipo_grupo' => 'EXTRA'],// Para 'Deporte (3° y 4°)'
        ];

        // Preparamos los datos para una única inserción, añadiendo los campos comunes
        $dataToInsert = array_map(function ($grupo) {
            // AQUÍ SE AÑADEN LOS CAMPOS FALTANTES A CADA REGISTRO
            $grupo['ciclo_escolar'] = '2025-2026';
            $grupo['estado'] = 'ACTIVO';
            $grupo['created_at'] = now();
            $grupo['updated_at'] = now();
            return $grupo;
        }, $grupos);

        // Insertamos todos los registros en una sola consulta
        DB::table('grupos')->insert($dataToInsert);
    }
}
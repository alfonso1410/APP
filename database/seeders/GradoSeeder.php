<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class GradoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buena práctica: limpiar la tabla antes de llenarla para evitar duplicados
       

        $grados = [
            // --- GRADOS REGULARES ---

            // Nivel Preescolar (nivel_id: 1)
            ['nivel_id' => 1, 'nombre' => 'Primero Preescolar',  'orden' => 1, 'tipo_grado' => 'REGULAR'],
            ['nivel_id' => 1, 'nombre' => 'Segundo Preescolar', 'orden' => 2, 'tipo_grado' => 'REGULAR'],
            ['nivel_id' => 1, 'nombre' => 'Tercero Preescolar',  'orden' => 3, 'tipo_grado' => 'REGULAR'],
            
            // Nivel Primaria (nivel_id: 2)
            ['nivel_id' => 2, 'nombre' => 'Primero', 'orden' => 1, 'tipo_grado' => 'REGULAR'],
            ['nivel_id' => 2, 'nombre' => 'Segundo', 'orden' => 2, 'tipo_grado' => 'REGULAR'],
            ['nivel_id' => 2, 'nombre' => 'Tercero', 'orden' => 3, 'tipo_grado' => 'REGULAR'],
            ['nivel_id' => 2, 'nombre' => 'Cuarto',  'orden' => 4, 'tipo_grado' => 'REGULAR'],
            ['nivel_id' => 2, 'nombre' => 'Quinto',  'orden' => 5, 'tipo_grado' => 'REGULAR'],
            ['nivel_id' => 2, 'nombre' => 'Sexto',   'orden' => 6, 'tipo_grado' => 'REGULAR'],

            // --- GRADOS EXTRACURRICULARES (PSEUDO-GRADOS) ---

            // Nivel Preescolar (nivel_id: 1)
            ['nivel_id' => 1, 'nombre' => 'Yoga Preescolar', 'orden' => 10, 'tipo_grado' => 'EXTRA'],
            ['nivel_id' => 1, 'nombre' => 'Deporte Preescolar', 'orden' => 11, 'tipo_grado' => 'EXTRA'],
            
            // Nivel Primaria (nivel_id: 2)
            ['nivel_id' => 2, 'nombre' => 'Yoga (1° y 2°)',   'orden' => 12, 'tipo_grado' => 'EXTRA'],
            ['nivel_id' => 2, 'nombre' => 'Baile (1° y 2°)',    'orden' => 13, 'tipo_grado' => 'EXTRA'],
            ['nivel_id' => 2, 'nombre' => 'Deporte (1° y 2°)', 'orden' => 14, 'tipo_grado' => 'EXTRA'],
            ['nivel_id' => 2, 'nombre' => 'Yoga (3° y 4°)',   'orden' => 15, 'tipo_grado' => 'EXTRA'],
            ['nivel_id' => 2, 'nombre' => 'Yoga (5° y 6°)',   'orden' => 16, 'tipo_grado' => 'EXTRA'],
        ];

        foreach ($grados as $grado) {
            DB::table('grados')->insert([
                'nombre' => $grado['nombre'],
                'orden' => $grado['orden'], // <-- Valor para la nueva columna
                'nivel_id' => $grado['nivel_id'],
                'tipo_grado' => $grado['tipo_grado'], // <-- Valor para la nueva columna
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
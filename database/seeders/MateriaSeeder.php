<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MateriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      $materias = [
            // Materias básicas comunes
            ['materia_id' => 1, 'nombre' => 'Español'],
            ['materia_id' => 2, 'nombre' => 'Matemáticas'],
            ['materia_id' => 3, 'nombre' => 'Ciencias Naturales'],
            ['materia_id' => 4, 'nombre' => 'Historia'],
            ['materia_id' => 5, 'nombre' => 'Geografía'],
            ['materia_id' => 6, 'nombre' => 'Formación Cívica y Ética'],
            ['materia_id' => 7, 'nombre' => 'Educación Artística'],
            ['materia_id' => 8, 'nombre' => 'Educación Física'],
        ];
        foreach ($materias as $materia) {
            DB::table('materias')->updateOrInsert(
                ['materia_id' => $materia['materia_id']],
                [
                    'nombre' => $materia['nombre'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

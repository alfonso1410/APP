<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grados = [
            //grados preescolar
            ['nivel_id' => 1, 'nombre' => 'Primero Preescolar'],
            ['nivel_id' => 1, 'nombre' => 'Segundo Preescolar'],
            ['nivel_id' => 1, 'nombre' => 'Tercero Preescolar'],
            //grados primaria
            ['nivel_id' => 2, 'nombre' => 'Primero'],
            ['nivel_id' => 2, 'nombre' => 'Segundo'],
            ['nivel_id' => 2, 'nombre' => 'Tercero'],
            ['nivel_id' => 2, 'nombre' => 'Cuarto'],
            ['nivel_id' => 2, 'nombre' => 'Quinto'],
            ['nivel_id' => 2, 'nombre' => 'Sexto'],
        ];

        foreach ($grados as $grado) {
            DB::table('grados')->insert([
                'nivel_id' => $grado['nivel_id'],
                'nombre' => $grado['nombre'],
                'Created_at' => now(),
                'Updated_at' => now(),
             ]);
        }
    }
}

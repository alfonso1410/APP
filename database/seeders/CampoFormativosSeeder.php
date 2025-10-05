<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CampoFormativosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campos = [
            ['campo_id'=> 1, 'nombre' => 'Lenguajes'],
            ['campo_id'=> 2, 'nombre' => 'Saberes y Pensamiento Científico'],
            ['campo_id'=> 3, 'nombre' => 'Etica, Naturaleza y Sociedad'],
            ['campo_id'=> 4, 'nombre' => 'De lo humano y comunitario'],
        ];

        foreach ($campos as $campo) {
            DB::table('campos_formativos')->updateOrInsert(
                ['campo_id' => $campo['campo_id']],
                ['nombre' => $campo['nombre'],
                'Created_at' => now(),
                'Updated_at' => now(),
            ]);
        }
    }
}


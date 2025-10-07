<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NivelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('niveles')->insert([
            ['nivel_id' => '1', 'nombre' => 'Preescolar','created_at' => now(), 'updated_at' => now()],
            ['nivel_id' => '2', 'nombre' => 'Primaria','created_at' => now(), 'updated_at' => now()],
        ]);
    }
}

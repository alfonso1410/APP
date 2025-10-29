<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CicloEscolar; 

class GrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- INICIO DE MODIFICACIÓN ---
        // 1. Obtén el ID del ciclo escolar activo (o el que quieras usar)
        $cicloActivo = CicloEscolar::where('nombre', '2025-2026')->first();

        // Si no existe, no podemos continuar (o puedes crearlo aquí)
        if (!$cicloActivo) {
            $this->command->error('Ciclo Escolar 2025-2026 no encontrado. Ejecuta CicloEscolarSeeder primero.');
            return;
        }
        $cicloId = $cicloActivo->ciclo_escolar_id;
        // --- FIN DE MODIFICACIÓN ---

        $grupos = [
            // --- GRUPOS REGULARES ---
            ['grado_id' => 1, 'nombre_grupo' => 'A', 'tipo_grupo' => 'REGULAR'],
            ['grado_id' => 2, 'nombre_grupo' => 'A', 'tipo_grupo' => 'REGULAR'],
            ['grado_id' => 4, 'nombre_grupo' => 'A', 'tipo_grupo' => 'REGULAR'],
            ['grado_id' => 4, 'nombre_grupo' => 'B', 'tipo_grupo' => 'REGULAR'],
            ['grado_id' => 5, 'nombre_grupo' => 'A', 'tipo_grupo' => 'REGULAR'],
            // --- GRUPOS EXTRACURRICULARES ---
            ['grado_id' => 10, 'nombre_grupo' => 'Único', 'tipo_grupo' => 'EXTRA'],
            ['grado_id' => 12, 'nombre_grupo' => 'A', 'tipo_grupo' => 'EXTRA'],
            ['grado_id' => 12, 'nombre_grupo' => 'B', 'tipo_grupo' => 'EXTRA'],
            ['grado_id' => 14, 'nombre_grupo' => 'Único', 'tipo_grupo' => 'EXTRA'],
        ];

        $dataToInsert = array_map(function ($grupo) use ($cicloId) { // <-- Pasa $cicloId
            // --- INICIO DE MODIFICACIÓN ---
            // 2. Añade ciclo_escolar_id y quita el string viejo
            $grupo['ciclo_escolar_id'] = $cicloId;
            // unset($grupo['ciclo_escolar']); // Ya no existe
            // --- FIN DE MODIFICACIÓN ---
            
            $grupo['estado'] = 'ACTIVO';
            $grupo['created_at'] = now();
            $grupo['updated_at'] = now();
            return $grupo;
        }, $grupos);

        // Limpia la tabla antes de insertar para evitar duplicados si se ejecuta varias veces
        DB::table('grupos')->delete(); 
        DB::table('grupos')->insert($dataToInsert);
    }
}
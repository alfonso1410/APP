<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CicloEscolar; 
use App\Models\Periodo;
class PeriodoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- INICIO DE MODIFICACIÓN ---
        // 1. Obtén el ID del ciclo escolar activo
        $cicloActivo = CicloEscolar::where('nombre', '2025-2026')->first();

        if (!$cicloActivo) {
            $this->command->error('Ciclo Escolar 2025-2026 no encontrado para Periodos.');
            return;
        }
        $cicloId = $cicloActivo->ciclo_escolar_id;
        // --- FIN DE MODIFICACIÓN ---
        
        $periodos = [
            [
                'nombre' => 'Trimestre 1',
                'fecha_inicio' => '2025-09-01',
                'fecha_fin' => '2025-12-05',
            ],
            [
                'nombre' => 'Trimestre 2',
                'fecha_inicio' => '2025-12-06',
                'fecha_fin' => '2026-03-15',
            ],
            [
                'nombre' => 'Trimestre 3',
                'fecha_inicio' => '2026-03-16',
                'fecha_fin' => '2026-07-10',
            ],
        ];

        // Limpia la tabla ANTES de insertar (para este ciclo específico)
        DB::table('periodos')->where('ciclo_escolar_id', $cicloId)->delete(); 

        foreach ($periodos as $periodo) {
            DB::table('periodos')->insert( // Usamos insert simple ahora
                [
                    // --- INICIO DE MODIFICACIÓN ---
                    'ciclo_escolar_id' => $cicloId, // <-- 2. Añade el ID
                    // --- FIN DE MODIFICACIÓN ---
                    'nombre' => $periodo['nombre'],
                    'fecha_inicio' => $periodo['fecha_inicio'],
                    'fecha_fin' => $periodo['fecha_fin'],
                    'estado' => 'ABIERTO', // Asegura el estado
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
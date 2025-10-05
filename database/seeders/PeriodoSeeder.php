<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class PeriodoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      // Se insertan periodos de ejemplo para pruebas
        // Usamos fechas claramente distintas para evitar conflictos en la clave UNIQUE compuesta.
        
        $periodos = [
            [
                'nombre' => 'Trimestre 1',
                'fecha_inicio' => '2025-09-01',
                'fecha_fin' => '2025-12-05',
                // estado: 'ABIERTO' (Se usa el valor por defecto de la migración)
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

        foreach ($periodos as $periodo) {
            // Usamos updateOrInsert con las columnas únicas como clave de búsqueda
            DB::table('periodos')->updateOrInsert(
                // Clave de Búsqueda: nombre y fecha_inicio
                [
                    'nombre' => $periodo['nombre'],
                    'fecha_inicio' => $periodo['fecha_inicio']
                ],
                // Valores a Insertar/Actualizar
                [
                    'fecha_fin' => $periodo['fecha_fin'],
                    'created_at' => now(),
                    'updated_at' => now(),
                    // El campo 'estado' tomará 'ABIERTO' por defecto
                    'estado' => 'ABIERTO',
                ]
            );
        }
    }
}

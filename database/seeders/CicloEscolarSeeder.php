<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CicloEscolar; 

class CicloEscolarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run(): void
    {
        // Asegúrate de que el estado inicial sea 'ACTIVO' para uno
        CicloEscolar::updateOrCreate(
            ['nombre' => '2025-2026'], // Busca por nombre
            [ // Datos a insertar o actualizar
                'fecha_inicio' => '2025-08-25',
                'fecha_fin' => '2026-07-15',
                'estado' => 'ACTIVO',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Puedes añadir ciclos pasados si quieres probar el historial
        CicloEscolar::updateOrCreate(
            ['nombre' => '2024-2025'],
            [
                'fecha_inicio' => '2024-08-26',
                'fecha_fin' => '2025-07-16',
                'estado' => 'CERRADO', // Marcar como cerrado
                'created_at' => now()->subYear(),
                'updated_at' => now()->subYear(),
            ]
        );
    }
}
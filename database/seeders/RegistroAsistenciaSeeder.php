<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Periodo; // Asegúrate de que esta línea esté si usas Periodo
// Asegúrate de que esta línea esté si usas DB::table

class RegistroAsistenciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fecha_asistencia = '2025-09-02';

        // --- INICIO DE MODIFICACIÓN ---
        // 2. Busca el periodo que corresponde a la fecha de asistencia
        $periodo = Periodo::where('fecha_inicio', '<=', $fecha_asistencia)
                          ->where('fecha_fin', '>=', $fecha_asistencia)
                          ->first(); // Encuentra el primer periodo que coincida

        // Si no se encuentra un periodo para esa fecha, no podemos continuar
        if (!$periodo) {
            $this->command->error("No se encontró un periodo activo para la fecha {$fecha_asistencia}. Asegúrate de que PeriodoSeeder se ejecutó y las fechas son correctas.");
            return;
        }
        $periodoId = $periodo->periodo_id;
        // --- FIN DE MODIFICACIÓN ---

        $registros = [
            // Alumno 1 en Grupo 1: Asistencia de ESPAÑOL
            [
                'alumno_id' => 1,
                'grupo_id' => 1,
                'fecha' => $fecha_asistencia,
                'tipo_asistencia' => 'PRESENTE',
                'idioma' => 'ESPAÑOL'
            ],
            // Alumno 2 en Grupo 1: Asistencia de ESPAÑOL
            [
                'alumno_id' => 2,
                'grupo_id' => 1,
                'fecha' => $fecha_asistencia,
                'tipo_asistencia' => 'FALTA',
                'idioma' => 'ESPAÑOL'
            ],
            // Alumno 1 en Grupo 1: Segunda asistencia del día (INGLES)
            [
                'alumno_id' => 1,
                'grupo_id' => 1,
                'fecha' => $fecha_asistencia,
                'tipo_asistencia' => 'PRESENTE',
                'idioma' => 'INGLES'
            ],
        ];

        // Limpia registros existentes para esta fecha/periodo/idioma (opcional pero recomendado)
        // DB::table('registro_asistencia')->where('fecha', $fecha_asistencia)->where('periodo_id', $periodoId)->delete();

        foreach ($registros as $registro) {
            DB::table('registro_asistencia')->updateOrInsert(
                // Clave de Búsqueda COMPLETA
                [
                    'alumno_id' => $registro['alumno_id'],
                    'grupo_id' => $registro['grupo_id'],
                    'fecha' => $registro['fecha'],
                    'idioma' => $registro['idioma'],
                    // --- INICIO DE MODIFICACIÓN ---
                    'periodo_id' => $periodoId, // <-- 3. Añade periodo_id a la búsqueda
                    // --- FIN DE MODIFICACIÓN ---
                ],
                // Valores a Insertar/Actualizar
                [
                    'tipo_asistencia' => $registro['tipo_asistencia'],
                    'created_at' => now(),
                    'updated_at' => now(),
                    // El periodo_id también debe estar aquí si es un nuevo registro
                    'periodo_id' => $periodoId
                ]
            );
        }
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CampoFormativosSeeder extends Seeder
{
    /*
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buscamos el ID del nivel "Primaria"
        //    (Esto asume que el seeder de Niveles ya se ejecutó)
        $nivelPrimariaId = DB::table('niveles')->where('nombre', 'Primaria')->value('nivel_id');

        // 2. Verificación de seguridad (buena práctica)
        if (!$nivelPrimariaId) {
            // Si no encuentra el ID, detiene el seeder y avisa en la consola.
            $this->command->error('No se encontró el Nivel "Primaria".');
            $this->command->info('Asegúrate de ejecutar el NivelSeeder primero (ej: db:seed --class=NivelSeeder).');
            return; // Detiene este seeder
        }

        // 3. Tus campos formativos
        $campos = [
            ['campo_id'=> 1, 'nombre' => 'Lenguajes'],
            ['campo_id'=> 2, 'nombre' => 'Saberes y Pensamiento Científico'],
            ['campo_id'=> 3, 'nombre' => 'Etica, Naturaleza y Sociedad'],
            ['campo_id'=> 4, 'nombre' => 'De lo humano y comunitario'],
        ];

        // 4. Iteramos e insertamos/actualizamos
        foreach ($campos as $campo) {
            DB::table('campos_formativos')->updateOrInsert(
                // Columna(s) para buscar (la condición WHERE)
                ['campo_id' => $campo['campo_id']],
                
                // Datos completos para insertar o actualizar
                [
                    'nombre' => $campo['nombre'],
                    'nivel_id' => $nivelPrimariaId, // <-- ¡Aquí se asigna!
                    'created_at' => now(), // Corregido a minúsculas
                    'updated_at' => now(), // Corregido a minúsculas
                ]
            );
        }
    }
}
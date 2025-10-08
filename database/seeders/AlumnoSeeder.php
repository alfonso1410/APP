<?php

namespace Database\Seeders;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class AlumnoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $alumnos = [
            [
                'alumno_id' => 1,
                'nombres' => 'Sofía',
                'apellido_paterno' => 'Gómez',
                'apellido_materno' => 'Pérez',
                'fecha_nacimiento' => Carbon::parse('2018-03-10'),
                'curp' => 'GOSP010310HDFNNN01', // CURP simulado
                'estado_alumno' => '1',
            ],
            [
                'alumno_id' => 2,
                'nombres' => 'Ricardo',
                'apellido_paterno' => 'Hernández',
                'apellido_materno' => 'López',
                'fecha_nacimiento' => Carbon::parse('2017-11-20'),
                'curp' => 'HELN021120HDFNNN02',
                'estado_alumno' => '1',
            ],
            [
                'alumno_id' => 3,
                'nombres' => 'Emilia',
                'apellido_paterno' => 'Vázquez',
                'apellido_materno' => 'Suarez',
                'fecha_nacimiento' => Carbon::parse('2019-06-05'),
                'curp' => 'VAFE030605HDFNNN03',
                'estado_alumno' => '0',
            ],
        ];

        foreach ($alumnos as $alumno) {
            DB::table('alumnos')->updateOrInsert(
                ['curp' => $alumno['curp']], // Buscamos por la clave única (CURP)
                [
                    'nombres' => $alumno['nombres'],
                    'apellido_paterno' => $alumno['apellido_paterno'],
                    'apellido_materno' => $alumno['apellido_materno'],
                    'fecha_nacimiento' => $alumno['fecha_nacimiento'],
                    'estado_alumno' => $alumno['estado_alumno'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

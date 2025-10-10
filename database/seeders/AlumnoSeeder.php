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
                'estado_alumno' => 'ACTIVO',
            ],
            [
                'alumno_id' => 2,
                'nombres' => 'Ricardo',
                'apellido_paterno' => 'Hernández',
                'apellido_materno' => 'López',
                'fecha_nacimiento' => Carbon::parse('2017-11-20'),
                'curp' => 'HELN021120HDFNNN02',
                'estado_alumno' => 'ACTIVO',
            ],
            [
                'alumno_id' => 3,
                'nombres' => 'Emilia',
                'apellido_paterno' => 'Vázquez',
                'apellido_materno' => 'Suarez',
                'fecha_nacimiento' => Carbon::parse('2019-06-05'),
                'curp' => 'VAFE030605HDFNNN03',
                'estado_alumno' => 'INACTIVO',
            ],// --- 7 Nuevos Alumnos ---
            [
                'nombres' => 'Mateo',
                'apellido_paterno' => 'Jiménez',
                'apellido_materno' => 'García',
                'fecha_nacimiento' => Carbon::parse('2017-08-15'),
                'curp' => 'JIGM170815HDFNNN04',
                'estado_alumno' => 'ACTIVO',
            ],
            [
                'nombres' => 'Valentina',
                'apellido_paterno' => 'Martínez',
                'apellido_materno' => 'Rodríguez',
                'fecha_nacimiento' => Carbon::parse('2017-01-25'),
                'curp' => 'MARV170125MDFNNN05',
                'estado_alumno' => 'ACTIVO',
            ],
            [
                'nombres' => 'Leonardo',
                'apellido_paterno' => 'Pérez',
                'apellido_materno' => 'Sánchez',
                'fecha_nacimiento' => Carbon::parse('2016-04-30'),
                'curp' => 'PESL160430HDFNNN06',
                'estado_alumno' => 'ACTIVO',
            ],
            [
                'nombres' => 'Isabella',
                'apellido_paterno' => 'González',
                'apellido_materno' => 'Cruz',
                'fecha_nacimiento' => Carbon::parse('2016-09-12'),
                'curp' => 'GOCI160912MDFNNN07',
                'estado_alumno' => 'ACTIVO',
            ],
            [
                'nombres' => 'Santiago',
                'apellido_paterno' => 'Ramírez',
                'apellido_materno' => 'Flores',
                'fecha_nacimiento' => Carbon::parse('2015-02-18'),
                'curp' => 'RAFS150218HDFNNN08',
                'estado_alumno' => 'ACTIVO',
            ],
            [
                'nombres' => 'Camila',
                'apellido_paterno' => 'Mendoza',
                'apellido_materno' => 'Morales',
                'fecha_nacimiento' => Carbon::parse('2015-07-22'),
                'curp' => 'MEMC150722MDFNNN09',
                'estado_alumno' => 'ACTIVO',
            ],
            [
                'nombres' => 'Diego',
                'apellido_paterno' => 'Rojas',
                'apellido_materno' => 'Ortiz',
                'fecha_nacimiento' => Carbon::parse('2018-12-01'),
                'curp' => 'ROOD181201HDFNNN10',
                'estado_alumno' => 'ACTIVO',
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

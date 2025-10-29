<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

    
        $this->call([
            Userseeder::class,
            NivelSeeder::class,
            GradoSeeder::class,
            CampoFormativosSeeder::class,
            CicloEscolarSeeder::class,
            MateriaSeeder::class,
            PeriodoSeeder::class,
            CatalogoCriteriosSeeder::class,
            AlumnoSeeder::class,
            GrupoSeeder::class,
            EstructuraCurricularSeeder::class,
            MateriaCriteriosSeeder::class,
            GrupoMateriaMaestroSeeder::class,
            AsignacionGrupalSeeder::class,
            CalificacionSeeder::class,
            RegistroAsistenciaSeeder::class,
            
        ]);
    }
}

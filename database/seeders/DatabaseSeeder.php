<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear Roles
        $roleAdmin = Role::create(['name' => 'Admin']);
        $roleMaestro = Role::create(['name' => 'Maestro']);

        // Crear tu Usuario Administrador
        $adminUser = User::create([
            'name' => 'Pavel Noel',
            'apellido_paterno' => 'Domínguez',
            'apellido_materno' => 'Reyes',
            'email' => 'noel02_2003@hotmail.com',
            'password' => Hash::make('password'),
            'activo' => 1,
        ]);

        // Asignar Rol de Admin
        $adminUser->assignRole($roleAdmin);
    }
}
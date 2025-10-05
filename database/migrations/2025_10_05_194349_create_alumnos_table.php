<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::create('alumnos', function (Blueprint $table) {
            // Clave Primaria Autoincremental
            $table->id('alumno_id');
            
            // Datos de Nombre
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100);
            
            // Datos de Identificación
            $table->date('fecha_nacimiento')->nullable(); // Permitimos que la fecha sea NULL si no es inmediata
            $table->string('curp', 18)->unique();
            
            // Estado del Alumno
            $table->string('estado_alumno', 20)->default('ACTIVO'); // ACTIVO, INACTIVO, BAJA, etc.

            // Columna de timestamps estándar de Laravel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};

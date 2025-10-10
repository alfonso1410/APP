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
        Schema::create('grupos', function (Blueprint $table) {
            // Clave Primaria
            $table->id('grupo_id');
            
            // Clave Foránea a la tabla 'grados'
            $table->unsignedBigInteger('grado_id');
            
            // === CORRECCIÓN PRINCIPAL ===
            // Se cambió el nombre de la columna para seguir la convención.
            $table->string('nombre', 50); // Ej: "A", "B", "Ajedrez"
            
            $table->string('ciclo_escolar', 10); // Ej: "2025-2026"

            // === MEJORA DE BUENA PRÁCTICA ===
            // Usar ENUM para restringir los valores a un set predefinido.
            // Esto garantiza la integridad de los datos a nivel de base de datos.
            $table->enum('estado', ['ACTIVO', 'CERRADO'])->default('ACTIVO');
            $table->enum('tipo_grupo', ['REGULAR', 'EXTRA']);

            // Timestamps para created_at y updated_at
            $table->timestamps();

            // === CORRECCIÓN EN LA RESTRICCIÓN ÚNICA ===
            // La restricción ahora usa la columna corregida 'nombre'.
            $table->unique(['nombre', 'grado_id', 'ciclo_escolar']);

            // Definición de la Clave Foránea
            $table->foreign('grado_id')
                  ->references('grado_id')
                  ->on('grados')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
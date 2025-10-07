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
            // Clave Primaria Autoincremental
            $table->id('grupo_id');
            
            // Clave Foránea a la tabla 'grados' (INT NOT NULL)
            $table->unsignedBigInteger('grado_id'); 
            
            // Campos Descriptivos
            $table->string('nombre_grupo', 50); // Ej: "A", "B", "Unico"
            $table->string('ciclo_escolar', 10); // Ej: "2025-2026"
            
            // Estado y Tipo (con valores por defecto)
            $table->string('estado', 20)->default('ACTIVO'); // ACTIVO o CERRADO
            $table->string('tipo_grupo', 20); // REGULAR o EXTRA (Extracurricular)

            // Restricción ÚNICA COMPUESTA: No puede haber dos grupos con el mismo nombre y grado en el mismo ciclo.
            $table->unique(['nombre_grupo', 'grado_id', 'ciclo_escolar']);

            // Definición de la Clave Foránea
            $table->foreign('grado_id')
                  ->references('grado_id')
                  ->on('grados')
                  ->onDelete('restrict'); 
            $table->timestamps();
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

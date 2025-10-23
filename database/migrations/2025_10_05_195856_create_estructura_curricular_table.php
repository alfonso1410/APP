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
        Schema::create('estructura_curricular', function (Blueprint $table) {
            // Claves Foráneas que componen la Clave Primaria
            $table->unsignedBigInteger('grado_id');
            $table->unsignedBigInteger('campo_id');
            $table->unsignedBigInteger('materia_id');
            
            // Ponderación
            $table->decimal('campo_ponderacion', 5, 2)->default(1.0);

            // Definición de la Clave Primaria Compuesta
            $table->primary(['grado_id', 'campo_id', 'materia_id'], 'pk_estructura_curricular');

            // Definición de Claves Foráneas
            $table->foreign('grado_id')->references('grado_id')->on('grados')->onDelete('cascade');
            
            // --- CORRECCIÓN: Cambiar onDelete a restrict para campos_formativos ---
            $table->foreign('campo_id')
                  ->references('campo_id')
                  ->on('campos_formativos')
                  ->onDelete('restrict'); // <-- CORREGIDO

            // Ya estaba corregido para materias
            $table->foreign('materia_id')
                  ->references('materia_id')
                  ->on('materias')
                  ->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estructura_curricular');
    }
};
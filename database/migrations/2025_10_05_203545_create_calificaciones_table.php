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
         Schema::create('calificaciones', function (Blueprint $table) {
            // Clave Primaria Sustituta
            $table->id('calificacion_id');
            
            // Claves Foráneas (unsignedBigInteger para compatibilidad)
            $table->unsignedBigInteger('alumno_id');
            $table->unsignedBigInteger('materia_criterio_id'); 
            $table->unsignedBigInteger('periodo_id');
            
            // Dato Transaccional
            $table->decimal('calificacion_obtenida', 4, 2); 

            // Definición de Claves Foráneas
            $table->foreign('alumno_id')->references('alumno_id')->on('alumnos')->onDelete('cascade');
            $table->foreign('materia_criterio_id')->references('materia_criterio_id')->on('materia_criterios')->onDelete('restrict');
            $table->foreign('periodo_id')->references('periodo_id')->on('periodos')->onDelete('restrict');
            
            // Restricción ÚNICA LÓGICA: Un alumno solo puede tener una calificación por UN criterio, en UN periodo dado.
            $table->unique(['alumno_id', 'materia_criterio_id', 'periodo_id'], 'unique_calificacion_alumno_criterio');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};

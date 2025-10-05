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
         Schema::create('asignacion_grupal', function (Blueprint $table) {
            // Clave Primaria Sustituta
            $table->id('asignacion_id');
            
            // Claves Foráneas (unsignedBigInteger para compatibilidad)
            $table->unsignedBigInteger('alumno_id');
            $table->unsignedBigInteger('grupo_id');
            
            // Estado de la Asignación
            $table->boolean('es_actual')->default(true); // Para historial

            // Definición de Claves Foráneas
            $table->foreign('alumno_id')->references('alumno_id')->on('alumnos')->onDelete('cascade');
            $table->foreign('grupo_id')->references('grupo_id')->on('grupos')->onDelete('cascade');

            // Restricción ÚNICA: Un alumno solo puede estar en un grupo una vez.
            $table->unique(['alumno_id', 'grupo_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignacion_grupal');
    }
};

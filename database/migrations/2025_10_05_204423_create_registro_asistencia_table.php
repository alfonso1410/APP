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
          Schema::create('registro_asistencia', function (Blueprint $table) {
              // Clave Primaria Sustituta
              $table->id('registro_asistencia_id');
              
              // Claves Foráneas (unsignedBigInteger para compatibilidad)
              $table->unsignedBigInteger('alumno_id');
              $table->unsignedBigInteger('grupo_id'); 
              
              // Dato Transaccional
              $table->date('fecha');
              
              // --- CORRECCIÓN 1: 'turno' se cambia por 'idioma' ---
              $table->string('idioma', 20);
              
              $table->string('tipo_asistencia', 20); // 'PRESENTE', 'FALTA', 'RETARDO', etc.

              // Definición de Claves Foráneas
              $table->foreign('alumno_id')->references('alumno_id')->on('alumnos')->onDelete('cascade');
              $table->foreign('grupo_id')->references('grupo_id')->on('grupos')->onDelete('restrict');
              
              // --- CORRECCIÓN 2: La restricción única ahora usa 'idioma' ---
              $table->unique(['alumno_id', 'grupo_id', 'fecha', 'idioma'], 'unique_asistencia_completa');
              
              $table->timestamps();
          });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_asistencia');
    }
};
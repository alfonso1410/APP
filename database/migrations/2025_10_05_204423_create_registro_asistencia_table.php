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
            $table->id('registro_asistencia_id');

            // --- INICIO DE MODIFICACIÓN ---
            $table->unsignedBigInteger('periodo_id'); // <-- 1. Nueva columna
            // --- FIN DE MODIFICACIÓN ---

            $table->unsignedBigInteger('alumno_id');
            $table->unsignedBigInteger('grupo_id');

            $table->date('fecha');
            $table->string('idioma', 20);
            $table->string('tipo_asistencia', 20); // 'PRESENTE', 'FALTA', 'RETARDO', etc.

            // Llaves foráneas existentes
            $table->foreign('alumno_id')->references('alumno_id')->on('alumnos')->onDelete('cascade');
            $table->foreign('grupo_id')->references('grupo_id')->on('grupos')->onDelete('restrict');

            // --- INICIO DE MODIFICACIÓN ---
            // 2. Nueva llave foránea para periodo
            $table->foreign('periodo_id')->references('periodo_id')->on('periodos')->onDelete('restrict');
            // --- FIN DE MODIFICACIÓN ---

            // Restricción única existente (no necesita periodo_id usualmente)
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
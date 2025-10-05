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
        Schema::create('periodos', function (Blueprint $table) {
            // Clave Primaria Autoincremental
            $table->id('periodo_id');
            
            // Nombre del Periodo (VARCHAR(50), NO NULO). Ej: "Trimestre 1"
            $table->string('nombre', 50);

            // Fechas (DATE, NO NULO)
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado', 20)->default('ABIERTO');
            // Restricción ÚNICA compuesta: Un mismo nombre de periodo no puede iniciar en la misma fecha dos veces.
            $table->unique(['nombre', 'fecha_inicio']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodos');
    }
};

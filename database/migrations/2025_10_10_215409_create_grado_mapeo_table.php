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
           Schema::create('grado_mapeo', function (Blueprint $table) {
        $table->id();

        // Columna para el ID del grado extracurricular (ej. "Yoga 1-2")
        $table->foreignId('extra_grado_id')
              ->constrained('grados', 'grado_id') // Se conecta a la tabla 'grados' en su columna 'grado_id'
              ->onDelete('cascade'); // Si se borra el grado extra, se borra el mapeo

        // Columna para el ID del grado regular (ej. "Primero")
        $table->foreignId('regular_grado_id')
              ->constrained('grados', 'grado_id') // También se conecta a la tabla 'grados'
              ->onDelete('cascade');

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grado_mapeo');
    }
};

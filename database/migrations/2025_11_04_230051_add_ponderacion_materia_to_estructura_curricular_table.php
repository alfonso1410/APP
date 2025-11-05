<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     * Añadimos la columna para la ponderación de la materia
     * dentro de su campo formativo.
     */
    public function up(): void
    {
        Schema::table('estructura_curricular', function (Blueprint $table) {
            $table->decimal('ponderacion_materia', 5, 2)
                  ->default(0.00)
                  ->after('materia_id'); // La colocamos después de la materia
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::table('estructura_curricular', function (Blueprint $table) {
            // Verificamos si la columna existe antes de borrarla
            if (Schema::hasColumn('estructura_curricular', 'ponderacion_materia')) {
                $table->dropColumn('ponderacion_materia');
            }
        });
    }
};
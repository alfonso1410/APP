<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     * Esta migración ELIMINA la columna 'campo_ponderacion'.
     */
    public function up(): void
    {
        Schema::table('estructura_curricular', function (Blueprint $table) {
            // Verificamos si la columna existe antes de borrarla
            if (Schema::hasColumn('estructura_curricular', 'campo_ponderacion')) {
                $table->dropColumn('campo_ponderacion');
            }
        });
    }

    /**
     * Revierte las migraciones.
     * Si hacemos 'rollback', la volvemos a crear.
     */
    public function down(): void
    {
        Schema::table('estructura_curricular', function (Blueprint $table) {
            $table->decimal('campo_ponderacion', 8, 2)->default(0.0)->after('materia_id');
        });
    }
};
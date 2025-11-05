<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('ponderacion_campos', function (Blueprint $table) {
            $table->id('ponderacion_campo_id');
            
            // Apunta a 'ciclo_escolars' (PK: 'ciclo_escolar_id')
            $table->foreignId('ciclo_escolar_id')
                  ->constrained('ciclo_escolars', 'ciclo_escolar_id')
                  ->onDelete('cascade');
            
            // Apunta a 'grados' (PK: 'grado_id')
            $table->foreignId('grado_id')
                  ->constrained('grados', 'grado_id')
                  ->onDelete('cascade');
            
            // --- INICIO DE LA CORRECCIÓN ---
            // Apunta a 'campos_formativos' (PK: 'campo_id')
            $table->foreignId('campo_formativo_id')
                  ->constrained('campos_formativos', 'campo_id') // <-- LÍNEA CORREGIDA
                  ->onDelete('cascade');
            // --- FIN DE LA CORRECCIÓN ---
            
            $table->decimal('ponderacion', 5, 2)->default(0.00); 
            $table->timestamps();

            $table->unique(
                ['ciclo_escolar_id', 'grado_id', 'campo_formativo_id'], 
                'ponderacion_unica_ciclo_grado_campo'
            );
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('ponderacion_campos');
    }
};
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
            $table->id('periodo_id');
            
            // --- INICIO DE MODIFICACIÓN ---
            $table->unsignedBigInteger('ciclo_escolar_id'); // <-- 1. Nueva columna
            // --- FIN DE MODIFICACIÓN ---

            $table->string('nombre', 50);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado', 20)->default('ABIERTO');
            
            // --- INICIO DE MODIFICACIÓN ---
            // 2. Restricción única actualizada
            $table->unique(['ciclo_escolar_id', 'nombre']); 
            // $table->unique(['nombre', 'fecha_inicio']); // <-- 3. Vieja restricción eliminada
            // --- FIN DE MODIFICACIÓN ---

            $table->timestamps();

            // --- INICIO DE MODIFICACIÓN ---
            // 4. NUEVA Llave foránea para ciclo escolar
            $table->foreign('ciclo_escolar_id')
                  ->references('ciclo_escolar_id')
                  ->on('ciclo_escolars') // Asegúrate que tu tabla se llame así
                  ->onDelete('restrict');
            // --- FIN DE MODIFICACIÓN ---
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

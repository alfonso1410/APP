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
        Schema::create('grupos', function (Blueprint $table) {
            $table->id('grupo_id');
            $table->unsignedBigInteger('grado_id');
            
            // --- INICIO DE MODIFICACIÓN ---
            $table->unsignedBigInteger('ciclo_escolar_id'); // <-- 1. Nueva columna
            // --- FIN DE MODIFICACIÓN ---
            
            $table->string('nombre_grupo', 50); 
            // $table->string('ciclo_escolar', 10); // <-- 2. Columna vieja eliminada

            $table->enum('estado', ['ACTIVO', 'CERRADO'])->default('ACTIVO');
            $table->enum('tipo_grupo', ['REGULAR', 'EXTRA']);
            $table->timestamps();

            // --- INICIO DE MODIFICACIÓN ---
            // 3. Restricción única actualizada
            $table->unique(['nombre_grupo', 'grado_id', 'ciclo_escolar_id']); 
            // --- FIN DE MODIFICACIÓN ---

            // 4. Llave foránea para grado (sin cambios)
            $table->foreign('grado_id')
                  ->references('grado_id')
                  ->on('grados')
                  ->onDelete('restrict');
            
            // --- INICIO DE MODIFICACIÓN ---
            // 5. NUEVA Llave foránea para ciclo escolar
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
        Schema::dropIfExists('grupos');
    }
};
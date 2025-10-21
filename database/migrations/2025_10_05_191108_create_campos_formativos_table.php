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
        Schema::create('campos_formativos', function (Blueprint $table) {
            $table->id('campo_id');
            
            // --- CORRECCIÓN ---
            // Quitamos el ->unique() de aquí.
            $table->string('nombre', 100); 
            // --- FIN CORRECCIÓN ---

            // Tu llave foránea (esto está correcto, asumiendo que niveles.nivel_id es unsignedBigInteger)
            $table->unsignedBigInteger('nivel_id'); 

            $table->foreign('nivel_id')
                  ->references('nivel_id')
                  ->on('niveles')
                  ->onDelete('restrict'); // Evita borrar un nivel si tiene campos

            // --- LÍNEA AÑADIDA ---
            // Creamos un índice único compuesto.
            // Esto permite "Lenguajes" con nivel_id=1 Y "Lenguajes" con nivel_id=2
            // pero NO "Lenguajes" con nivel_id=1 dos veces.
            $table->unique(['nombre', 'nivel_id']);
            // --- FIN LÍNEA AÑADIDA ---

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campos_formativos');
    }
};
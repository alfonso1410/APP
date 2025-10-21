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
            $table->string('nombre', 100)->unique();
            
            // --- LÍNEAS AÑADIDAS ---
            // Añadimos la llave foránea
            $table->unsignedBigInteger('nivel_id'); 

            // Creamos la relación
            $table->foreign('nivel_id')
                  ->references('nivel_id')  // Apunta a 'nivel_id' en la tabla 'niveles'
                  ->on('niveles')
                  ->onDelete('restrict'); // Evita borrar un nivel si tiene campos
            // --- FIN LÍNEAS AÑADIDAS ---

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
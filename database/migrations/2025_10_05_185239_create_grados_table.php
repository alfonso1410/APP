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
        Schema::create('grados', function (Blueprint $table) {
            $table->id('grado_id');
            $table->string('nombre', 50);
            // --- CAMPOS AÑADIDOS ---
            
            // 1. Para ordenar correctamente (Primero, Segundo, Tercero, etc.)
            $table->unsignedTinyInteger('orden')->default(0);

            $table->unsignedBigInteger('nivel_id');

            // 2. Para diferenciar entre grados académicos y extracurriculares
            $table->string('tipo_grado', 20)->default('REGULAR');
            
            // --- FIN DE CAMPOS AÑADIDOS ---
            $table->timestamps();
            //columna tabla niveles, referencia su llave, y su tabla niveles y no se puede borrar un nivel si tiene grados asociados
            $table->foreign('nivel_id')->references('nivel_id')->on('niveles')->onDelete('restrict');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grados');
    }
};

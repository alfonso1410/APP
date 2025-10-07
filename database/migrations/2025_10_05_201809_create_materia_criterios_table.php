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
        Schema::create('materia_criterios', function (Blueprint $table) {
            // Clave Primaria Sustituta
            $table->id('materia_criterio_id'); 
            
            // Claves Foráneas (unsignedBigInteger para compatibilidad)
            $table->unsignedBigInteger('materia_id');
            $table->unsignedBigInteger('catalogo_criterio_id');
            
            // Atributos
            $table->decimal('ponderacion', 5, 2); 
            $table->boolean('incluido_en_promedio')->default(true); // Usamos boolean para TRUE/FALSE

            // Definición de Claves Foráneas
            $table->foreign('materia_id')->references('materia_id')->on('materias')->onDelete('cascade');
            $table->foreign('catalogo_criterio_id')->references('catalogo_criterio_id')->on('catalogo_criterios')->onDelete('restrict');

            // Restricción ÚNICA: Un criterio solo puede asignarse una vez por materia.
            $table->unique(['materia_id', 'catalogo_criterio_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materia_criterios');
    }
};

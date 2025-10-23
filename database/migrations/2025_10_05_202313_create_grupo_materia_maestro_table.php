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
        Schema::create('grupo_materia_maestro', function (Blueprint $table) {
            // Clave Primaria Sustituta
            $table->id(); // Laravel le llama 'id' por defecto
            
            // Claves Foráneas
            $table->unsignedBigInteger('grupo_id');
            $table->unsignedBigInteger('materia_id');
            $table->unsignedBigInteger('maestro_id')->nullable(); // Referencia a Usuarios
            
            // Definición de Claves Foráneas
            $table->foreign('grupo_id')->references('grupo_id')->on('grupos')->onDelete('cascade');
            
            // --- CORRECCIÓN: Cambiar onDelete a restrict para materias ---
            $table->foreign('materia_id')
                  ->references('materia_id')
                  ->on('materias')
                  ->onDelete('restrict'); // <-- CORREGIDO

            $table->foreign('maestro_id')->references('id')->on('users')->onDelete('restrict'); 

            // Restricción ÚNICA: Solo una materia puede tener un maestro asignado por grupo.
            $table->unique(['grupo_id', 'materia_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo_materia_maestro');
    }
};
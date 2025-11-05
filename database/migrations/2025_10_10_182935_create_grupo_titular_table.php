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
        Schema::create('grupo_titular', function (Blueprint $table) {
            
            // --- Columnas ---
            $table->unsignedBigInteger('grupo_id');
            $table->string('idioma', 20); // 'ESPAÑOL' o 'INGLES'
            
            // ID del Maestro Titular
            $table->unsignedBigInteger('maestro_titular_id')->nullable(); 
            
            // ID del Maestro Auxiliar (NUEVO)
            $table->unsignedBigInteger('maestro_auxiliar_id')->nullable(); 
            
            $table->timestamps();

            // --- Llaves y Restricciones ---
            
            // La llave primaria lógica: Solo una entrada por grupo e idioma.
            $table->primary(['grupo_id', 'idioma']); 
            
            // Llaves foráneas
            $table->foreign('grupo_id')->references('grupo_id')->on('grupos')->onDelete('cascade');
            
            // Llave foránea para el Titular
            $table->foreign('maestro_titular_id')
                  ->references('id')->on('users')
                  ->onDelete('set null'); // Si se borra el user, se pone null

            // Llave foránea para el Auxiliar (NUEVO)
            $table->foreign('maestro_auxiliar_id')
                  ->references('id')->on('users')
                  ->onDelete('set null'); // Si se borra el user, se pone null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo_titular');
    }
};
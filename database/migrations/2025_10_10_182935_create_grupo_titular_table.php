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
            $table->unsignedBigInteger('maestro_id');
            
            // --- CORRECCIÓN: Se quitó ->after('maestro_id') ---
            $table->string('idioma', 20); // 'ESPAÑOL' o 'INGLES'
            $table->timestamps();

            // --- Llaves y Restricciones ---
            $table->primary(['grupo_id', 'maestro_id']);
            $table->unique(['grupo_id', 'idioma']);
            
            $table->foreign('grupo_id')->references('grupo_id')->on('grupos')->onDelete('cascade');
            $table->foreign('maestro_id')->references('id')->on('users')->onDelete('cascade');
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
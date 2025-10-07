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
         Schema::create('materias', function (Blueprint $table) {
            // Clave Primaria Autoincremental
            $table->id('materia_id');
            
            // Nombre de la Materia (VARCHAR(100), ÚNICO, NO NULO)
            $table->string('nombre', 100)->unique();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};

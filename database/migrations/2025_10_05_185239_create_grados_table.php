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
            $table->unsignedBigInteger('nivel_id');
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

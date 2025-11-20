<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up()
{
    Schema::create('grupo_maestro_complementario', function (Blueprint $table) {
        $table->id();

        // 1. Definimos la columna explicitamente (asegúrate que sea del mismo tipo que en tu tabla grupos, usualmente unsignedBigInteger o integer)
        $table->unsignedBigInteger('grupo_id'); 
        
        // 2. Definimos la llave foránea manualmente indicando la columna destino
        $table->foreign('grupo_id')
              ->references('grupo_id') // <--- Aquí especificamos el nombre real de la PK en la tabla grupos
              ->on('grupos')
              ->onDelete('cascade'); // Opcional: si borras el grupo, se borra la relación

        // Hacemos lo mismo para el usuario (este usualmente sí es 'id')
        $table->foreignId('user_id')->constrained('users');

        $table->timestamps();

        // Evitar duplicados
        $table->unique(['grupo_id', 'user_id'], 'grupo_maestro_unique'); 
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo_maestro_complementario');
    }
};

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
    Schema::create('observaciones_campos_formativos', function (Blueprint $table) {
        $table->id();

        // 1. El ALUMNO al que se está evaluando
        $table->foreignId('alumno_id')
              ->constrained('alumnos', 'alumno_id') // Asumiendo que la PK de alumnos es 'alumno_id'
              ->onDelete('cascade');

        // 2. El PERIODO en el que se está evaluando
        $table->foreignId('periodo_id')
              ->constrained('periodos', 'periodo_id') // Asumiendo que la PK de periodos es 'periodo_id'
              ->onDelete('cascade');

        // 3. El CAMPO FORMATIVO que se está evaluando
        //    Usa la PK 'campo_id' de tu tabla existente
        $table->foreignId('campo_id')
              ->constrained('campos_formativos', 'campo_id') // PK de tu tabla 'campos_formativos'
              ->onDelete('cascade');

        // 4. EL TEXTO LIBRE DEL MAESTRO
        //    Aquí es donde el maestro escribe los logros
        $table->text('observaciones')->nullable(); 

        $table->timestamps();

        // Opcional: Una llave única para evitar duplicados
        // $table->unique(['alumno_id', 'periodo_id', 'campo_id']);
    });
}

/**
 * Reverse the migrations.
 */
public function down(): void
{
        Schema::dropIfExists('observaciones_campos_formativos');
    }
};

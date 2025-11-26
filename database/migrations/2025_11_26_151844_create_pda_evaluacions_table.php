<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pda_evaluaciones', function (Blueprint $table) {
            $table->id(); // ID interno de esta tabla (autoincrement)
            
            // Relación con Alumnos (Usando alumno_id)
            $table->unsignedBigInteger('alumno_id');
            $table->foreign('alumno_id')->references('alumno_id')->on('alumnos')->onDelete('cascade');

            // Relación con Periodos (Usando periodo_id)
            $table->unsignedBigInteger('periodo_id');
            $table->foreign('periodo_id')->references('periodo_id')->on('periodos')->onDelete('cascade');

            // Relación con Materias (Usando materia_id) - Puede ser nulo
            $table->unsignedBigInteger('materia_id')->nullable();
            $table->foreign('materia_id')->references('materia_id')->on('materias')->onDelete('cascade');

            // Relación con Campos Formativos (Usando campo_id según tu modelo EstructuraCurricular)
            // IMPORTANTE: Asumo que la PK en campos_formativos es 'campo_id' basándome en tu EstructuraCurricular
            $table->unsignedBigInteger('campo_formativo_id')->nullable();
            // Si tu tabla real se llama 'campos_formativos' y su llave es 'campo_id':
            $table->foreign('campo_formativo_id')->references('campo_id')->on('campos_formativos')->onDelete('cascade');

            $table->text('observacion')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pda_evaluaciones');
    }
};
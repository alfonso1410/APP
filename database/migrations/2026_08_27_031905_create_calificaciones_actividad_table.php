<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calificaciones_actividad', function (Blueprint $table) {
            $table->id('calificacion_actividad_id');
            $table->foreignId('actividad_id')->constrained('actividades_materia', 'actividad_id')->cascadeOnDelete();
            $table->foreignId('alumno_id')->constrained('alumnos', 'alumno_id')->restrictOnDelete();

            $table->decimal('calificacion_obtenida',5,2)->nullable();
            $table->enum('estado_entrega',['ENTREGADO','NO_ENTREGO','FALTA_JUSTIFICADA'])->default('ENTREGADO');
            $table->string('observaciones')->nullable();

            $table->timestamps();
            $table->unique(['actividad_id','alumno_id'], 'uk_actividad_alumno');
        });

        DB::statement('ALTER TABLE calificaciones_actividad ADD CONSTRAINT chk_calificacion_obtenida_positiva CHECK(calificacion_obtenida IS NULL OR calificacion_obtenida >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificaciones_actividad');
    }
};

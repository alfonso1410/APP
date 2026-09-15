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
        Schema::create('actividades_materia', function (Blueprint $table) {
            $table->id('actividad_id');
            $table->foreignId('grupo_id')->constrained('grupos', 'grupo_id')->cascadeOnDelete();
            $table->foreignId('periodo_id')->constrained('periodos', 'periodo_id')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materias', 'materia_id')->cascadeOnDelete();
            $table->foreignId('materia_criterio_id')->constrained('materia_criterios', 'materia_criterio_id')->cascadeOnDelete();
            $table->foreignId('maestro_id')->constrained('users', 'id')->restrictOnDelete();

            $table->string('nombre_actividad', 150);
            $table->text('descripcion')->nullable();
            $table->date('fecha_actividad');
            $table->decimal('valor_maximo', 5, 2)->default(10.00);


            $table->foreignId('calculado_por')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->timestamp('calculado_en')->nullable();

            $table->index(['grupo_id', 'materia_id', 'periodo_id', 'materia_criterio_id'], 'idx_grupo_materia_periodo_criterio');
            $table->timestamps();
        });
        DB::statement('ALTER TABLE actividades_materia ADD CONSTRAINT chk_valor_maximo_positivo CHECK(valor_maximo > 0) ');
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividades_materia');
    }
};

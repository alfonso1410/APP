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
        $table->unsignedBigInteger('grupo_id');
        $table->unsignedBigInteger('maestro_id');
        
        $table->primary(['grupo_id', 'maestro_id']);
        
        $table->foreign('grupo_id')->references('grupo_id')->on('grupos')->onDelete('cascade');
        $table->foreign('maestro_id')->references('id')->on('users')->onDelete('cascade');
        $table->timestamps();
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

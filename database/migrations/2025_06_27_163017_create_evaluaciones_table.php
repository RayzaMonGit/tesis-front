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
        Schema::create('evaluaciones', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('postulacion_id')->nullable();
            $table->bigInteger('evaluador_id')->nullable();
            $table->float('puntaje_total')->nullable();
            $table->string('comentarios_generales')->nullable();
            $table->string('estado', 30)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->softDeletes();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluaciones');
    }
};

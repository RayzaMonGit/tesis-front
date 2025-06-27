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
        Schema::create('evaluacion_documentos', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('evaluacion_id')->nullable();
            $table->bigInteger('postulacion_documento_id')->nullable();
            $table->string('estado', 30)->nullable();
            $table->float('puntaje')->nullable();
            $table->string('comentarios')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluacion_documentos');
    }
};

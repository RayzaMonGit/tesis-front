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
        Schema::create('evaluacion_requisitos', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('evaluacion_id');
            $table->bigInteger('requisito_id')->nullable();
            $table->string('estado', 30)->nullable();
            $table->string('comentarios')->nullable();
            $table->boolean('es_requisito_ley')->nullable();
            $table->time('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->bigInteger('postulacion_documento_id')->nullable();
            $table->bigInteger('requisito_ley_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluacion_requisitos');
    }
};

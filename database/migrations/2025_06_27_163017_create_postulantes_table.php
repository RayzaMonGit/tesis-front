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
        Schema::create('postulantes', function (Blueprint $table) {
            $table->comment('Almacena la información de los postulantes a convocatorias académicas');
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->index('idx_postulantes_user_id');
            $table->timestamp('fecha_nacimiento')->nullable();
            $table->string('especialidad', 100)->nullable();
            $table->bigInteger('experiencia_años')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('grado_academico', 100)->nullable();
            $table->timestamp('fecha_postulacion')->nullable();

            $table->unique(['user_id'], 'uq_postulante_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postulantes');
    }
};

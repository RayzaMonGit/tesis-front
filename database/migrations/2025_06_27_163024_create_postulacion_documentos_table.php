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
        Schema::create('postulacion_documentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('postulacion_id');
            $table->string('nombre');
            $table->string('archivo')->nullable();
            $table->boolean('es_requisito_ley')->nullable()->default(false);
            $table->boolean('es_requisito_personalizado')->nullable()->default(false);
            $table->timestamps();
            $table->bigInteger('requisito_id')->nullable();
            $table->bigInteger('seccion_id')->nullable();
            $table->bigInteger('criterio_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postulacion_documentos');
    }
};

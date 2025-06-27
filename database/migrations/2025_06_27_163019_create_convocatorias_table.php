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
        Schema::create('convocatorias', function (Blueprint $table) {
            $table->comment('Convocatorias para puestos académicos');
            $table->bigIncrements('id');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('documento')->nullable();
            $table->string('area', 150)->nullable();
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->string('estado', 30)->default('activa');
            $table->integer('plazas_disponibles')->nullable()->default(1);
            $table->integer('sueldo_referencial')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->bigInteger('formulario_id')->nullable();
            $table->bigInteger('año_limite')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convocatorias');
    }
};

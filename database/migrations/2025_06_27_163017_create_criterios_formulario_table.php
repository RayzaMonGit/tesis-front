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
        Schema::create('criterios_formulario', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('seccion_id');
            $table->string('nombre');
            $table->integer('orden')->nullable()->default(0);
            $table->timestamps();
            $table->float('puntaje_por_item');
            $table->float('puntaje_maximo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criterios_formulario');
    }
};

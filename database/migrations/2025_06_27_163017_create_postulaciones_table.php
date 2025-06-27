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
        Schema::create('postulaciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('postulante_id');
            $table->bigInteger('convocatoria_id');
            $table->string('estado')->default('pendiente');
            $table->timestamps();
            $table->float('nota_preliminar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postulaciones');
    }
};

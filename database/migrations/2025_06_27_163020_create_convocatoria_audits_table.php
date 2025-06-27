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
        Schema::create('convocatoria_audits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('convocatoria_id');
            $table->bigInteger('user_id');
            $table->string('accion');
            $table->json('cambios')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convocatoria_audits');
    }
};

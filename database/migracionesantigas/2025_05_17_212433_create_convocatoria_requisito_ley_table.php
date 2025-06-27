<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('convocatoria_requisito_ley', function (Blueprint $table) {
        $table->id();
        $table->foreignId('convocatoria_id')->constrained('convocatorias')->onDelete('cascade');
        $table->foreignId('requisitos_ley_id')->constrained('requisitos_ley')->onDelete('cascade');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convocatoria_requisito_ley');
    }
};

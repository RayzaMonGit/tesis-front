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
            $table->id();
            $table->foreignId('postulacion_id')->constrained('postulaciones')->onDelete('cascade');

            $table->string('nombre'); // nombre visible
            $table->string('archivo'); // ruta del archivo
            $table->boolean('es_requisito_ley')->default(false);
            $table->boolean('es_requisito_personalizado')->default(false);
            $table->timestamps();
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

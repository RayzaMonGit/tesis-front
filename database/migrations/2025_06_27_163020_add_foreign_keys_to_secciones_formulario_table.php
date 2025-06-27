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
        Schema::table('secciones_formulario', function (Blueprint $table) {
            $table->foreign(['formulario_id'])->references(['id'])->on('formularios_evaluacion')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('secciones_formulario', function (Blueprint $table) {
            $table->dropForeign('secciones_formulario_formulario_id_foreign');
        });
    }
};

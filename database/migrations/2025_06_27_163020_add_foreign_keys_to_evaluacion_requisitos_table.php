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
        Schema::table('evaluacion_requisitos', function (Blueprint $table) {
            $table->foreign(['evaluacion_id'], 'evaluacion_requisitos_evaluacion_id_fkey')->references(['id'])->on('evaluaciones')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['requisito_id'], 'evaluacion_requisitos_requisito_id_fkey')->references(['id'])->on('requisitos')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['requisito_ley_id'], 'evaluacion_requisitos_requisito_ley_id_fkey')->references(['id'])->on('requisitos_ley')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluacion_requisitos', function (Blueprint $table) {
            $table->dropForeign('evaluacion_requisitos_evaluacion_id_fkey');
            $table->dropForeign('evaluacion_requisitos_requisito_id_fkey');
            $table->dropForeign('evaluacion_requisitos_requisito_ley_id_fkey');
        });
    }
};

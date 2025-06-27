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
        Schema::table('evaluacion_documentos', function (Blueprint $table) {
            $table->foreign(['evaluacion_id'], 'evaluacion_documentos_evaluacion_id_fkey')->references(['id'])->on('evaluaciones')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['postulacion_documento_id'], 'evaluacion_documentos_postulacion_documento_id_fkey')->references(['id'])->on('postulacion_documentos')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluacion_documentos', function (Blueprint $table) {
            $table->dropForeign('evaluacion_documentos_evaluacion_id_fkey');
            $table->dropForeign('evaluacion_documentos_postulacion_documento_id_fkey');
        });
    }
};

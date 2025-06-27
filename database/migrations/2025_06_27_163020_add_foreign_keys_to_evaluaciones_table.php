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
        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->foreign(['evaluador_id'], 'evaluaciones_evaluador_id_fkey')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['postulacion_id'], 'evaluaciones_postulacion_id_fkey')->references(['id'])->on('postulaciones')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->dropForeign('evaluaciones_evaluador_id_fkey');
            $table->dropForeign('evaluaciones_postulacion_id_fkey');
        });
    }
};

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
        Schema::table('postulaciones', function (Blueprint $table) {
            $table->foreign(['convocatoria_id'])->references(['id'])->on('convocatorias')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['postulante_id'])->references(['id'])->on('postulantes')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            $table->dropForeign('postulaciones_convocatoria_id_foreign');
            $table->dropForeign('postulaciones_postulante_id_foreign');
        });
    }
};

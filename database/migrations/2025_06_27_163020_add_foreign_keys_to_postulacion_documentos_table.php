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
        Schema::table('postulacion_documentos', function (Blueprint $table) {
            $table->foreign(['postulacion_id'])->references(['id'])->on('postulaciones')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('postulacion_documentos', function (Blueprint $table) {
            $table->dropForeign('postulacion_documentos_postulacion_id_foreign');
        });
    }
};

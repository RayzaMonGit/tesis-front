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
        Schema::table('criterios_formulario', function (Blueprint $table) {
            $table->foreign(['seccion_id'])->references(['id'])->on('secciones_formulario')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('criterios_formulario', function (Blueprint $table) {
            $table->dropForeign('criterios_formulario_seccion_id_foreign');
        });
    }
};

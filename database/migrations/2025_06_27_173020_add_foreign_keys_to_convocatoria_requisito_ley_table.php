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
        Schema::table('convocatoria_requisito_ley', function (Blueprint $table) {
            $table->foreign(['convocatoria_id'])->references(['id'])->on('convocatorias')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['requisitos_ley_id'])->references(['id'])->on('requisitos_ley')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('convocatoria_requisito_ley', function (Blueprint $table) {
            $table->dropForeign('convocatoria_requisito_ley_convocatoria_id_foreign');
            $table->dropForeign('convocatoria_requisito_ley_requisitos_ley_id_foreign');
        });
    }
};

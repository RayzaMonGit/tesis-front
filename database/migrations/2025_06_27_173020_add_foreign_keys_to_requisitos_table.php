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
        Schema::table('requisitos', function (Blueprint $table) {
            $table->foreign(['id_convocatoria'], 'requisitos_id_convocatoria_fkey')->references(['id'])->on('convocatorias')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requisitos', function (Blueprint $table) {
            $table->dropForeign('requisitos_id_convocatoria_fkey');
        });
    }
};

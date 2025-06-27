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
        Schema::table('convocatoria_comision', function (Blueprint $table) {
            $table->foreign(['convocatoria_id'])->references(['id'])->on('convocatorias')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('convocatoria_comision', function (Blueprint $table) {
            $table->dropForeign('convocatoria_comision_convocatoria_id_foreign');
            $table->dropForeign('convocatoria_comision_user_id_foreign');
        });
    }
};

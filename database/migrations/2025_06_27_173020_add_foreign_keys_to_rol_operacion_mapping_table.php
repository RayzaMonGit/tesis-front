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
        Schema::table('rol_operacion_mapping', function (Blueprint $table) {
            $table->foreign(['permission_id'], 'fk_rol_operacion_permission')->references(['id'])->on('permissions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['role_id'], 'fk_rol_operacion_role')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rol_operacion_mapping', function (Blueprint $table) {
            $table->dropForeign('fk_rol_operacion_permission');
            $table->dropForeign('fk_rol_operacion_role');
        });
    }
};

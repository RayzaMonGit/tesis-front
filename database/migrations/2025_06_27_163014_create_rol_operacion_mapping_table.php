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
        Schema::create('rol_operacion_mapping', function (Blueprint $table) {
            $table->comment('Tabla de mapeo entre ROL_OPERACION de tu diseño y las tablas de Laravel');
            $table->bigIncrements('id_rol_operacion');
            $table->bigInteger('role_id')->index('idx_rol_operacion_role');
            $table->bigInteger('permission_id')->index('idx_rol_operacion_permission');
            $table->timestamps();

            $table->unique(['role_id', 'permission_id'], 'uq_rol_operacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rol_operacion_mapping');
    }
};

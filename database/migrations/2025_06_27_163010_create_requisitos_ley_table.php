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
        Schema::create('requisitos_ley', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->text('descripcion')->nullable();
            $table->string('num', 5)->nullable();
            $table->string('req', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitos_ley');
    }
};

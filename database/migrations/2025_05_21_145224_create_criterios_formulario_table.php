<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('criterios_formulario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seccion_id')->constrained('secciones_formulario')->onDelete('cascade');
            $table->string('nombre');
            $table->float('puntaje')->default(0);
           // $table->integer('max_items')->default(1); // máximo número de veces que se puede aplicar
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('criterios_formulario');
    }
};

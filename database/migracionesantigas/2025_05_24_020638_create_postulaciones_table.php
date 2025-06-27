<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostulacionesTable extends Migration
{
    public function up(): void
    {
        Schema::create('postulaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('postulante_id')->constrained()->onDelete('cascade');
            $table->foreignId('convocatoria_id')->constrained()->onDelete('cascade');
            $table->enum('estado', [
                'pendiente',
                'en evaluación',
                'evaluado',
                'rechazado',
                'aprobado'
            ])->default('pendiente'); // Laravel se encargará, ya tienes el tipo en PG
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postulaciones');
    }
}

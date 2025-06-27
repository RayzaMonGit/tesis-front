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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->string('surname')->nullable()->comment('Apellido del usuario');
            $table->string('telefono', 50)->nullable()->comment('Número de teléfono del usuario');
            $table->text('direccion')->nullable()->comment('Dirección física del usuario');
            $table->string('avatar', 250)->nullable();
            $table->bigInteger('role_id')->nullable();
            $table->string('designacion', 350)->nullable();
            $table->timeTz('deleted_at')->nullable();
            $table->string('gender', 6)->nullable();
            $table->string('tipo_doc', 50)->nullable();
            $table->string('n_doc', 25)->nullable();
            $table->string('verification_code')->nullable();
            $table->boolean('is_verified')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

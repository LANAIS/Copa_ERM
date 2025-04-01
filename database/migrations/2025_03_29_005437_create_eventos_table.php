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
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('lugar')->nullable();
            $table->string('banner')->nullable();
            $table->string('slug')->unique();
            $table->datetime('fecha_inicio');
            $table->datetime('fecha_fin');
            $table->datetime('inicio_inscripciones');
            $table->datetime('fin_inscripciones');
            $table->enum('estado', ['proximamente', 'abierto', 'cerrado', 'finalizado'])->default('proximamente');
            $table->boolean('publicado')->default(false);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
}; 
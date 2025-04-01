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
        Schema::create('homologaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('robot_id')->constrained('robots');
            $table->foreignId('categoria_evento_id')->constrained('categoria_eventos');
            $table->foreignId('juez_id')->nullable()->constrained('users');
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->enum('resultado', ['aprobado', 'rechazado'])->nullable();
            $table->decimal('peso', 10, 2)->nullable();
            $table->decimal('ancho', 10, 2)->nullable();
            $table->decimal('largo', 10, 2)->nullable();
            $table->decimal('alto', 10, 2)->nullable();
            $table->json('dimensiones')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            // No puede haber dos homologaciones para el mismo robot y categoría de evento
            $table->unique(['robot_id', 'categoria_evento_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homologaciones');
    }
};

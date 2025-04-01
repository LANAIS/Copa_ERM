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
        Schema::create('inscripcion_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained()->onDelete('cascade');
            $table->foreignId('categoria_evento_id')->constrained()->onDelete('cascade');
            $table->foreignId('equipo_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('robot_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('estado', ['pendiente', 'confirmada', 'rechazada', 'cancelada'])->default('pendiente');
            $table->text('notas_participante')->nullable();
            $table->text('notas_admin')->nullable();
            $table->string('codigo_confirmacion')->nullable();
            $table->decimal('monto_pagado', 10, 2)->default(0);
            $table->string('comprobante_pago')->nullable();
            $table->datetime('fecha_confirmacion')->nullable();
            $table->datetime('fecha_pago')->nullable();
            $table->timestamps();
            
            // Índice único para evitar duplicados de inscripción para el mismo equipo y categoría
            $table->unique(['categoria_evento_id', 'equipo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripcion_eventos');
    }
};

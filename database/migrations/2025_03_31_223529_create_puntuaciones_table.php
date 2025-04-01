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
        Schema::create('puntuaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enfrentamiento_id')->constrained('enfrentamientos')->onDelete('cascade');
            $table->foreignId('equipo_id')->constrained('equipos')->onDelete('cascade');
            $table->float('puntos', 8, 2)->default(0);
            $table->float('penalizacion', 8, 2)->default(0);
            $table->float('total', 8, 2)->default(0);
            $table->text('notas')->nullable();
            $table->foreignId('juez_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Índices para mejorar el rendimiento de las consultas
            $table->index(['enfrentamiento_id', 'equipo_id']);
            $table->index('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puntuaciones');
    }
};

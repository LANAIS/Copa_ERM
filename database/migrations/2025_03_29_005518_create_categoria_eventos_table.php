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
        Schema::create('categoria_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained()->onDelete('cascade');
            $table->foreignId('categoria_id')->constrained()->onDelete('cascade');
            $table->text('reglas_especificas')->nullable();
            $table->text('requisitos')->nullable();
            $table->integer('participantes_min')->default(1);
            $table->integer('participantes_max')->default(10);
            $table->integer('cupo_limite')->nullable();
            $table->integer('inscritos')->default(0);
            $table->decimal('precio_inscripcion', 10, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->boolean('inscripciones_abiertas')->default(false);
            $table->timestamps();
            
            // Índice único para evitar duplicados
            $table->unique(['evento_id', 'categoria_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria_eventos');
    }
};

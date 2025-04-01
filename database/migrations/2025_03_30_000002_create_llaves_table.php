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
        Schema::create('llaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_evento_id')->constrained('categoria_eventos');
            $table->enum('tipo_fixture', ['todos_contra_todos', 'eliminacion_directa', 'suizo'])->default('eliminacion_directa');
            $table->json('estructura')->nullable();
            $table->boolean('finalizado')->default(false);
            $table->timestamps();
            
            // Solo puede haber una llave por categoría de evento
            $table->unique(['categoria_evento_id']);
        });
        
        Schema::create('enfrentamientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('llave_id')->constrained('llaves');
            $table->integer('ronda');
            $table->integer('posicion');
            $table->foreignId('equipo1_id')->nullable()->constrained('equipos');
            $table->foreignId('equipo2_id')->nullable()->constrained('equipos');
            $table->foreignId('ganador_id')->nullable()->constrained('equipos');
            $table->integer('puntaje_equipo1')->nullable();
            $table->integer('puntaje_equipo2')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            // No puede haber dos enfrentamientos con misma llave, ronda y posición
            $table->unique(['llave_id', 'ronda', 'posicion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enfrentamientos');
        Schema::dropIfExists('llaves');
    }
}; 
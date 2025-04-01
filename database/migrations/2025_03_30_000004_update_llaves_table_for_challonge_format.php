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
        Schema::table('llaves', function (Blueprint $table) {
            // Modificar el campo tipo_fixture con más opciones
            $table->dropColumn('tipo_fixture');
        });

        Schema::table('llaves', function (Blueprint $table) {
            $table->enum('tipo_fixture', [
                'eliminacion_directa',      // Single elimination
                'eliminacion_doble',        // Double elimination
                'todos_contra_todos',       // Round Robin
                'suizo',                    // Swiss
                'grupos',                   // Group Stage + Elimination
                'fase_grupos_eliminacion'   // Group Stage + Elimination
            ])->default('eliminacion_directa')->after('categoria_evento_id');
            
            // Opciones adicionales para configurar el torneo
            $table->json('opciones_torneo')->nullable()->after('estructura');
            
            // Estado específico del torneo
            $table->enum('estado_torneo', [
                'pendiente',
                'en_curso',
                'pausado',
                'finalizado'
            ])->default('pendiente')->after('finalizado');
            
            // Información de cabezas de serie (seeding)
            $table->boolean('usar_cabezas_serie')->default(false)->after('estado_torneo');
        });
        
        // Actualizar la tabla de enfrentamientos para agregar más información
        Schema::table('enfrentamientos', function (Blueprint $table) {
            // Número de juego (match number) para referenciar como en Challonge
            $table->integer('numero_juego')->nullable()->after('posicion');
            
            // Datos adicionales del enfrentamiento
            $table->json('datos_adicionales')->nullable()->after('observaciones');
            
            // Grupo (para torneos con fase de grupos)
            $table->string('grupo')->nullable()->after('ronda');
            
            // Fase del torneo
            $table->enum('fase', [
                'grupos',
                'winners',
                'losers',
                'final'
            ])->nullable()->after('grupo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enfrentamientos', function (Blueprint $table) {
            $table->dropColumn(['numero_juego', 'datos_adicionales', 'grupo', 'fase']);
        });
        
        Schema::table('llaves', function (Blueprint $table) {
            $table->dropColumn(['opciones_torneo', 'estado_torneo', 'usar_cabezas_serie']);
            $table->dropColumn('tipo_fixture');
        });
        
        Schema::table('llaves', function (Blueprint $table) {
            $table->enum('tipo_fixture', [
                'todos_contra_todos',
                'eliminacion_directa',
                'suizo'
            ])->default('eliminacion_directa')->after('categoria_evento_id');
        });
    }
}; 
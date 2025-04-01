<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inscripcion_eventos', function (Blueprint $table) {
            // Primero asegurarse de que el campo fecha_evento_id exista
            if (!Schema::hasColumn('inscripcion_eventos', 'fecha_evento_id')) {
                $table->unsignedBigInteger('fecha_evento_id')->nullable()->after('categoria_evento_id');
                $table->foreign('fecha_evento_id')->references('id')->on('fecha_eventos')->onDelete('cascade');
            }
            
            // Eliminar la restricción única existente (usando try-catch)
            try {
                $table->dropUnique('inscripcion_eventos_evento_id_equipo_id_unique');
            } catch (\Exception $e) {
                // Si el índice no existe, ignorar el error
                \Illuminate\Support\Facades\Log::info('No se pudo eliminar el índice: ' . $e->getMessage());
            }
            
            // Crear la nueva restricción única que incluye fecha_evento_id y categoria_evento_id
            // Usando un nombre más corto para el índice
            $table->unique(['evento_id', 'equipo_id', 'fecha_evento_id', 'categoria_evento_id'], 'inscripcion_evento_unique_fields');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscripcion_eventos', function (Blueprint $table) {
            // Eliminar la nueva restricción
            try {
                $table->dropUnique('inscripcion_evento_unique_fields');
            } catch (\Exception $e) {
                // Ignorar si no existe
            }
            
            // Restaurar la restricción original
            try {
                $table->unique(['evento_id', 'equipo_id'], 'inscripcion_eventos_evento_id_equipo_id_unique');
            } catch (\Exception $e) {
                // Ignorar si ya existe
            }
        });
    }
};

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
        Schema::table('inscripcion_eventos', function (Blueprint $table) {
            // Eliminar la restricción única anterior
            $table->dropUnique(['categoria_evento_id', 'equipo_id']);
            
            // Agregar la nueva restricción única basada en evento y equipo
            $table->unique(['evento_id', 'equipo_id'], 'inscripcion_eventos_evento_id_equipo_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscripcion_eventos', function (Blueprint $table) {
            // Eliminar la nueva restricción única
            $table->dropUnique('inscripcion_eventos_evento_id_equipo_id_unique');
            
            // Restaurar la restricción única anterior
            $table->unique(['categoria_evento_id', 'equipo_id']);
        });
    }
};

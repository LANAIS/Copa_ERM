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
        Schema::table('registrations', function (Blueprint $table) {
            // Eliminar el índice único que está causando conflictos
            $table->dropUnique(['team_id', 'robot_id', 'competition_event_id']);
            
            // También eliminar cualquier otra versión del índice único que pueda existir
            $table->dropUnique(['equipo_id', 'robot_id', 'competition_event_id']);
            $table->dropUnique(['equipo_id', 'robot_id', 'competition_id']);
            
            // Crear un índice único más específico que incluya la fecha del evento
            $table->unique(['equipo_id', 'robot_id', 'evento_id', 'categoria_evento_id', 'fecha_evento_id'], 'registrations_unique_complete');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Eliminar el nuevo índice
            $table->dropUnique('registrations_unique_complete');
            
            // Restaurar el índice original
            $table->unique(['equipo_id', 'robot_id', 'competition_event_id']);
        });
    }
};

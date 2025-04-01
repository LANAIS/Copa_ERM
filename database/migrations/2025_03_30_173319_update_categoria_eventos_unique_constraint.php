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
        Schema::table('categoria_eventos', function (Blueprint $table) {
            // Eliminar la restricción única existente
            $table->dropUnique(['evento_id', 'categoria_id']);
            
            // Agregar una nueva restricción única que incluya fecha_evento_id
            $table->unique(['evento_id', 'categoria_id', 'fecha_evento_id'], 'categoria_eventos_evento_categoria_fecha_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categoria_eventos', function (Blueprint $table) {
            // Eliminar la nueva restricción
            $table->dropUnique('categoria_eventos_evento_categoria_fecha_unique');
            
            // Restaurar la restricción original
            $table->unique(['evento_id', 'categoria_id']);
        });
    }
};

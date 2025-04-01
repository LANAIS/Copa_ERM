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
            // Añadir el nuevo campo para almacenar los robots participantes
            $table->json('robots_participantes')->nullable()->comment('JSON array con IDs de robots y estado de homologación');
            
            // La columna robot_id se mantendrá por compatibilidad, pero ya no será necesaria
            // ya que ahora todos los robots del equipo se registrarán automáticamente
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscripcion_eventos', function (Blueprint $table) {
            $table->dropColumn('robots_participantes');
        });
    }
};

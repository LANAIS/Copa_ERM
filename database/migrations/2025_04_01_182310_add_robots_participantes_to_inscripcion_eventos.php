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
            // Verificar si el campo no existe
            if (!Schema::hasColumn('inscripcion_eventos', 'robots_participantes')) {
                $table->json('robots_participantes')->nullable()->after('robot_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscripcion_eventos', function (Blueprint $table) {
            // Solo eliminar si existe
            if (Schema::hasColumn('inscripcion_eventos', 'robots_participantes')) {
                $table->dropColumn('robots_participantes');
            }
        });
    }
};

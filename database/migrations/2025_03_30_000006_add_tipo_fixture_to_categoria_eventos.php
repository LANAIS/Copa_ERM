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
            $table->enum('tipo_fixture', [
                'todos_contra_todos',
                'eliminacion_directa',
                'suizo'
            ])->default('eliminacion_directa')->after('estado_competencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categoria_eventos', function (Blueprint $table) {
            $table->dropColumn('tipo_fixture');
        });
    }
}; 
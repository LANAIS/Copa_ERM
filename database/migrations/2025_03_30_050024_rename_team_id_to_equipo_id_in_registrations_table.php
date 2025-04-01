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
            // Primero eliminamos la clave foránea
            $table->dropForeign(['team_id']);
            
            // Renombramos la columna
            $table->renameColumn('team_id', 'equipo_id');
            
            // Añadimos la nueva clave foránea
            $table->foreign('equipo_id')->references('id')->on('equipos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Primero eliminamos la clave foránea
            $table->dropForeign(['equipo_id']);
            
            // Renombramos la columna
            $table->renameColumn('equipo_id', 'team_id');
            
            // Añadimos la nueva clave foránea
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
        });
    }
};

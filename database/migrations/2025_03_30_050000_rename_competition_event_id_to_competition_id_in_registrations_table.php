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
            $table->dropForeign(['competition_event_id']);
            
            // Renombramos la columna
            $table->renameColumn('competition_event_id', 'competition_id');
            
            // Añadimos la nueva clave foránea
            $table->foreign('competition_id')->references('id')->on('competitions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Primero eliminamos la clave foránea
            $table->dropForeign(['competition_id']);
            
            // Renombramos la columna
            $table->renameColumn('competition_id', 'competition_event_id');
            
            // Añadimos la nueva clave foránea
            $table->foreign('competition_event_id')->references('id')->on('competition_events')->onDelete('cascade');
        });
    }
};

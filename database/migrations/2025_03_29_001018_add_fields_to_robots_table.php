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
        Schema::table('robots', function (Blueprint $table) {
            // Renombrar campo name por nombre (solo nombre)
            $table->renameColumn('name', 'nombre');
            
            // Eliminar campos que no usaremos
            $table->dropColumn(['model', 'description']);
            
            // Añadir nuevos campos
            $table->string('zona')->nullable()->after('nombre');
            $table->string('modalidad')->nullable()->after('zona');
            $table->string('capitan')->nullable()->after('modalidad');
            $table->string('foto')->nullable()->after('capitan');
            
            // Añadir relación con equipo (en español)
            $table->foreignId('equipo_id')->nullable()->after('foto');
            
            // Hacer nullable team_id para poder migrar posteriormente
            $table->foreignId('team_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('robots', function (Blueprint $table) {
            // Revertir cambios
            $table->renameColumn('nombre', 'name');
            $table->string('model')->nullable();
            $table->text('description')->nullable();
            $table->dropColumn(['zona', 'modalidad', 'capitan', 'foto', 'equipo_id']);
            $table->foreignId('team_id')->change();
        });
    }
};

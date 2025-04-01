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
            $table->foreignId('evento_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('categoria_evento_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fecha_evento_id')->nullable()->constrained('fecha_eventos')->nullOnDelete();
            $table->foreignId('inscripcion_evento_id')->nullable()->constrained('inscripcion_eventos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeign(['evento_id']);
            $table->dropForeign(['categoria_evento_id']);
            $table->dropForeign(['fecha_evento_id']);
            $table->dropForeign(['inscripcion_evento_id']);
            
            $table->dropColumn(['evento_id', 'categoria_evento_id', 'fecha_evento_id', 'inscripcion_evento_id']);
        });
    }
};

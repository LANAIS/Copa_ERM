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
            // Agregar la columna competition_event_id después de competition_id
            $table->foreignId('competition_event_id')
                ->nullable()
                ->after('competition_id')
                ->constrained('competition_events')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeign(['competition_event_id']);
            $table->dropColumn('competition_event_id');
        });
    }
};

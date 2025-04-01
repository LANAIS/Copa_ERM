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
            $table->foreignId('fecha_evento_id')->nullable()->after('evento_id')->constrained('fecha_eventos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categoria_eventos', function (Blueprint $table) {
            $table->dropForeign(['fecha_evento_id']);
            $table->dropColumn('fecha_evento_id');
        });
    }
};

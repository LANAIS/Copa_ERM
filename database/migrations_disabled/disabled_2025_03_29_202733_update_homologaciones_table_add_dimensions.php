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
        Schema::table('homologaciones', function (Blueprint $table) {
            // Añadir columnas específicas para dimensiones
            $table->decimal('ancho', 10, 2)->nullable()->after('peso');
            $table->decimal('largo', 10, 2)->nullable()->after('ancho');
            $table->decimal('alto', 10, 2)->nullable()->after('largo');
            
            // Añadir columna resultado
            $table->enum('resultado', ['aprobado', 'rechazado'])->nullable()->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homologaciones', function (Blueprint $table) {
            $table->dropColumn(['ancho', 'largo', 'alto', 'resultado']);
        });
    }
};

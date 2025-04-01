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
        Schema::create('inscripcion_participantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('categoria_id')->constrained()->onDelete('cascade');
            $table->string('nombre_equipo')->nullable();
            $table->string('nombre_institucion')->nullable();
            $table->string('nombre_robot')->nullable();
            $table->text('descripcion_proyecto')->nullable();
            $table->text('miembros_equipo')->nullable();
            $table->string('telefono_contacto')->nullable();
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
            $table->text('notas_participante')->nullable(); // Notas del participante
            $table->text('notas_admin')->nullable(); // Notas del administrador
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripcion_participantes');
    }
};

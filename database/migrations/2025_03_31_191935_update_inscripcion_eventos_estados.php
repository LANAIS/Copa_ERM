<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\InscripcionEvento;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero obtener todos los estados definidos en el modelo
        $estados = [
            InscripcionEvento::ESTADO_PENDIENTE,
            InscripcionEvento::ESTADO_CONFIRMADA,
            InscripcionEvento::ESTADO_PAGADA, 
            InscripcionEvento::ESTADO_RECHAZADA,
            InscripcionEvento::ESTADO_CANCELADA,
            InscripcionEvento::ESTADO_HOMOLOGADA,
            InscripcionEvento::ESTADO_PARTICIPANDO,
            InscripcionEvento::ESTADO_FINALIZADA,
        ];
        
        // Convertir el array a formato de enum para MySQL
        $enumString = "'" . implode("','", $estados) . "'";
        
        // Modificar la columna para aceptar los nuevos valores
        DB::statement("ALTER TABLE inscripcion_eventos MODIFY COLUMN estado ENUM($enumString) NOT NULL DEFAULT 'pendiente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a los valores originales
        $estadosOriginales = ["'pendiente'", "'confirmada'", "'rechazada'", "'cancelada'"];
        $enumString = implode(",", $estadosOriginales);
        
        DB::statement("ALTER TABLE inscripcion_eventos MODIFY COLUMN estado ENUM($enumString) NOT NULL DEFAULT 'pendiente'");
    }
};

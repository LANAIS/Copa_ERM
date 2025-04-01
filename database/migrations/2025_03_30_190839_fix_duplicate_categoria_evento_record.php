<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\CategoriaEvento;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero verificamos si hay un registro con ID 1 
        $categoriaEvento = DB::table('categoria_eventos')->where('id', 1)->first();
        
        if ($categoriaEvento) {
            // Buscamos si hay otros registros con el mismo evento_id y categoria_id
            $duplicados = DB::table('categoria_eventos')
                ->where('evento_id', $categoriaEvento->evento_id)
                ->where('categoria_id', $categoriaEvento->categoria_id)
                ->where('id', '!=', 1)
                ->where('fecha_evento_id', $categoriaEvento->fecha_evento_id)
                ->get();
            
            if ($duplicados->count() > 0) {
                // Modificamos el registro con ID 1 para establecer fecha_evento_id como NULL
                DB::table('categoria_eventos')
                    ->where('id', 1)
                    ->update(['fecha_evento_id' => null]);
                
                echo "Se ha actualizado el registro de CategoriaEvento con ID 1 para evitar duplicados.\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No necesitamos revertir nada, ya que hemos resuelto un conflicto de datos
    }
};

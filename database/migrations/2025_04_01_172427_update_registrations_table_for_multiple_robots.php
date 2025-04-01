<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero eliminamos cualquier índice o clave relacionada con robot_id
        Schema::table('registrations', function (Blueprint $table) {
            // Buscar índices únicos que puedan contener robot_id
            if (Schema::hasTable('registrations')) {
                $indexes = DB::select("SHOW INDEXES FROM registrations WHERE Column_name = 'robot_id'");
                foreach ($indexes as $index) {
                    if ($index->Key_name !== 'PRIMARY') {
                        $table->dropIndex($index->Key_name);
                    }
                }
            }
            
            // Si robot_id es una clave foránea, eliminarla
            if (Schema::hasTable('registrations')) {
                $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_NAME = 'registrations' 
                    AND COLUMN_NAME = 'robot_id' 
                    AND CONSTRAINT_NAME != 'PRIMARY' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL");
                
                foreach ($foreignKeys as $foreignKey) {
                    $table->dropForeign($foreignKey->CONSTRAINT_NAME);
                }
            }
        });
        
        // Ahora podemos cambiar el tipo de columna a text
        Schema::table('registrations', function (Blueprint $table) {
            // Crear una nueva columna robots_json para almacenar los datos JSON
            $table->text('robots_json')->nullable()->after('robot_id');
        });
        
        // Migrar datos existentes a la nueva columna
        DB::table('registrations')->get()->each(function ($registration) {
            if (!empty($registration->robot_id)) {
                DB::table('registrations')
                    ->where('id', $registration->id)
                    ->update(['robots_json' => json_encode([$registration->robot_id])]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Eliminar la columna añadida
            $table->dropColumn('robots_json');
        });
    }
};

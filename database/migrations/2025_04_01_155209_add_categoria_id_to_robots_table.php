<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Categoria;
use App\Models\Robot;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('robots', function (Blueprint $table) {
            $table->foreignId('categoria_id')->nullable()->after('categoria')->constrained('categorias');
        });

        // Migrar datos existentes basados en el campo 'categoria'
        $categoriasMapeadas = [
            'sumo' => Categoria::where('nombre', 'like', '%Sumo%')->first()?->id,
            'seguidor' => Categoria::where('nombre', 'like', '%Seguidor%')->first()?->id,
            'lucha' => Categoria::where('nombre', 'like', '%Lucha%')->first()?->id,
            'otro' => Categoria::where('nombre', 'like', '%Otro%')->first()?->id,
        ];

        foreach (Robot::all() as $robot) {
            if (isset($categoriasMapeadas[$robot->categoria])) {
                $robot->categoria_id = $categoriasMapeadas[$robot->categoria];
                $robot->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('robots', function (Blueprint $table) {
            $table->dropConstrainedForeignId('categoria_id');
        });
    }
};

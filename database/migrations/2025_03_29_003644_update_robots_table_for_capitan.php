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
            if (Schema::hasColumn('robots', 'capitan')) {
                $table->dropColumn('capitan');
            }
            
            $table->unsignedBigInteger('capitan')->nullable();
            
            $table->foreign('capitan')
                ->references('id')
                ->on('miembro_equipos')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('robots', function (Blueprint $table) {
            $table->dropForeign(['capitan']);
            
            $table->dropColumn('capitan');
            $table->string('capitan')->nullable();
        });
    }
};

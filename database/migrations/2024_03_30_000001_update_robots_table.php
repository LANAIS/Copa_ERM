<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('robots', function (Blueprint $table) {
            $table->string('descripcion')->nullable();
            $table->string('categoria');
            $table->string('imagen')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->dropColumn(['zona', 'modalidad', 'capitan', 'foto', 'team_id']);
        });
    }

    public function down()
    {
        Schema::table('robots', function (Blueprint $table) {
            $table->string('zona')->nullable();
            $table->string('modalidad')->nullable();
            $table->string('capitan')->nullable();
            $table->string('foto')->nullable();
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('set null');
            $table->dropColumn(['descripcion', 'categoria', 'imagen', 'user_id']);
        });
    }
}; 
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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('profesion_id')->nullable()->constrained('profesions')->onDelete('set null');
            $table->foreignId('persona_id')->nullable()->constrained('personas')->onDelete('set null');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->dateTime('ultimo_acceso')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};

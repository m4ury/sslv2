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
        Schema::create('programados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socio_id')->constrained('socios')->onDelete('cascade');
            $table->date('fecha_programada')->nullable();
            $table->time('hora')->nullable();
            $table->unsignedSmallInteger('duracion_min')->default(30);
            $table->enum('estado', ['asiste', 'no_asiste', 'reagendado'])->nullable();
            $table->string('observacion', 255)->nullable();
            $table->timestamps();

            $table->index(['fecha_programada', 'hora']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programados');
    }
};

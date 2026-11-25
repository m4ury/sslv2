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
        Schema::create('paciente_patologia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->foreignId('patologia_id')->constrained('patologias');
            $table->enum('estado', ['Activa', 'Egreso', 'Alta transitoria'])->default('Activa');
            $table->date('fecha_diagnostico')->nullable();
            $table->date('fecha_alta_tr')->nullable();
            $table->date('fecha_egreso')->nullable();
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patologia_socio');
    }
};

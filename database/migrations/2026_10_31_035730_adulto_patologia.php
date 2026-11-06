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
        Schema::create('adulto_patologia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adulto_id')->constrained('adultos');
            $table->foreignId('patologia_id')->constrained('patologias');
            $table->string('estado')->default('Activa');
            $table->date('fecha_diagnostico')->nullable();
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
        Schema::dropIfExists('adulto_patologia');
    }
};

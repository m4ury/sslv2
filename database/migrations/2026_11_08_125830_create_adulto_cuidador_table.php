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
        Schema::create('adulto_cuidador', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adulto_id')->constrained();
            $table->foreignId('cuidador_id')->constrained('cuidadores');
            $table->string('vinculo')->nullable(); // Hija, vecino, cuidador contratado...
            $table->boolean('es_principal')->default(false);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adulto_cuidador');
    }
};

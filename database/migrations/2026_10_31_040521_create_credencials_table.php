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
        Schema::create('credencials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adulto_id')->constrained('adultos')->onDelete('cascade');
            $table->string('numero', 255)->unique();
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_expiracion')->nullable();
            $table->enum('estado', ['vigente', 'vencido', 'anulado'])->default('vigente');
            $table->string('grado_discapacidad', 255)->nullable();
            $table->decimal('porcentaje_discapacidad', 5, 2)->nullable();
            $table->enum('tipo_discapacidad', ['fisica', 'sensorial', 'intelectual', 'otra'])->nullable();
            $table->string('organismo_emisor', 255)->nullable();
            $table->string('archivo_ruta', 255)->nullable();
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
        Schema::dropIfExists('credencials');
    }
};

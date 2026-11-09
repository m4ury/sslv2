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
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socio_id')->constrained('socios');
            $table->foreignId('config_cuota_id')->constrained('config_cuotas');
            $table->date('periodo')->nullable();
            $table->float('monto_cuota', 10, 2)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->enum('estado', ['Pendiente', 'Pagada', 'Vencida'])->default('Pendiente');
            $table->unique(['socio_id', 'config_cuota_id', 'periodo']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuotas');
    }
};

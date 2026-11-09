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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuota_id')->constrained('cuotas')->onDelete('cascade');
            $table->date('fecha_pago')->nullable();
            $table->float('monto_pagado', 10, 2)->nullable();
            $table->enum('medio_pago', ['efectivo', 'transferencia', 'debito'])->nullable();
            $table->string('numero_comprobante', 100)->nullable();
            $table->text('observacion')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('anulado_at')->nullable();
            $table->foreignId('anulado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('motivo_anulacion')->nullable();
            $table->index(['medio_pago', 'numero_comprobante']);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};

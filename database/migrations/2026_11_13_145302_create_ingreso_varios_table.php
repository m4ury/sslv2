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
        Schema::create('ingresos_varios', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->unsignedInteger('monto');
            $table->string('categoria');
            $table->string('descripcion');
            $table->string('origen')->nullable(); // quién entrega los fondos
            $table->string('tipo_documento');
            $table->string('num_documento', 50)->nullable();
            $table->string('medio_pago');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('anulado_at')->nullable();
            $table->foreignId('anulado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('motivo_anulacion')->nullable();
            $table->timestamps();

            $table->index('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingresos_varios');
    }
};

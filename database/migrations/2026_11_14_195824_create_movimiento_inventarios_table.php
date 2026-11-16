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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('articulo_id')->constrained('articulos');
            $table->string('tipo', 20); // entrada | baja | prestamo | devolucion
            $table->string('motivo')->nullable();
            $table->unsignedInteger('cantidad');
            $table->date('fecha');

            // Entradas
            $table->string('origen')->nullable(); // proveedor o donante
            $table->unsignedInteger('valor_unitario')->nullable();
            $table->string('tipo_documento')->nullable();
            $table->string('num_documento', 50)->nullable();

            // Préstamos y devoluciones
            $table->string('prestatario')->nullable();
            $table->string('prestatario_telefono', 30)->nullable();
            $table->date('fecha_compromiso')->nullable();
            $table->foreignId('movimiento_origen_id')->nullable()->constrained('movimientos_inventario');

            $table->text('observaciones')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('anulado_at')->nullable();
            $table->foreignId('anulado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('motivo_anulacion')->nullable();
            $table->timestamps();

            $table->index(['articulo_id', 'tipo']);
            $table->index('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};

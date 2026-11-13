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
        Schema::create('cierres_caja', function (Blueprint $table) {
            $table->id();
            $table->string('periodo', 7)->unique(); // AAAA-MM
            $table->bigInteger('saldo_inicial');
            $table->bigInteger('total_cuotas');
            $table->bigInteger('total_otros_ingresos')->default(0);
            $table->bigInteger('total_egresos');
            $table->bigInteger('saldo_final');
            $table->foreignId('cerrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cerrado_at');
            $table->timestamps();
        });

        // Los estados de cuentas consultan por fecha
        if (! Schema::hasIndex('pagos', ['fecha_pago'])) {
            Schema::table('pagos', fn (Blueprint $table) => $table->index('fecha_pago'));
        }
        /* if (! Schema::hasIndex('aportes', ['fecha_aporte'])) {
            Schema::table('aportes', fn (Blueprint $table) => $table->index('fecha_aporte'));
        } */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cierre_cajas');
    }
};

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
        Schema::create('medicions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('control_id')->constrained('controls')->nullable();
            $table->string('tipo', 40);
            $table->decimal('valor', 8, 2);
            $table->string('unidad', 20)->nullable();
            /* $table->float('presion_sistolica', 10, 2)->nullable();
            $table->float('presion_diastolica', 10, 2)->nullable();
            $table->float('hemoglobina_glicosilada', 10, 2)->nullable();
            $table->float('peso_actual', 10, 2)->nullable();
            $table->float('talla_actual', 10, 2)->nullable();
            $table->float('imc', 10, 2)->nullable();
            $table->string('clasificacion_imc', 100)->nullable(); */
            $table->unique(['control_id', 'tipo']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicions');
    }
};

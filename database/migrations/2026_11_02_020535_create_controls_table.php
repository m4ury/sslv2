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
        Schema::create('controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socio_id')->constrained('socios')->nullable();
            $table->foreignId('programado_id')->constrained('programados')->nullable();
            $table->foreignId('user_id')->constrained('users')->nullable();
            $table->date('fecha_control')->nullable();
            $table->enum('tipo_control', ['ingreso', 'control', 'egreso'])->nullable();
            $table->string('diagnostico_ingreso', 100)->nullable();
            $table->text('observacion')->nullable();
            $table->text('indicaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('controls');
    }
};

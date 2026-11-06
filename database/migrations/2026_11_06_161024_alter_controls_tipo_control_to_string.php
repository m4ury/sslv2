<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('controls', function (Blueprint $table) {
            $table->string('tipo_control')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasUnsupportedTypes = DB::table('controls')
            ->whereNotNull('tipo_control')
            ->whereNotIn('tipo_control', ['ingreso', 'control', 'egreso'])
            ->exists();

        if ($hasUnsupportedTypes) {
            throw new RuntimeException('Cannot restore the controls enum while ficha control types are stored.');
        }

        Schema::table('controls', function (Blueprint $table) {
            $table->enum('tipo_control', ['ingreso', 'control', 'egreso'])->nullable()->change();
        });
    }
};

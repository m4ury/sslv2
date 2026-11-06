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
            $table->foreignId('programado_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('controls')->whereNull('programado_id')->exists()) {
            throw new RuntimeException('Cannot require a programado for controls while unscheduled controls exist.');
        }

        Schema::table('controls', function (Blueprint $table) {
            $table->foreignId('programado_id')->nullable(false)->change();
        });
    }
};

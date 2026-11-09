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
        Schema::table('documentos', function (Blueprint $table) {
            $table->date('fecha_documento')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('documentos')
            ->whereNull('fecha_documento')
            ->update(['fecha_documento' => now()->toDateString()]);

        Schema::table('documentos', function (Blueprint $table) {
            $table->date('fecha_documento')->nullable(false)->change();
        });
    }
};

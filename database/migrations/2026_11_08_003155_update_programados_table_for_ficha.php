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
        Schema::table('programados', function (Blueprint $table) {
            $table->string('tipo_control')->nullable()->after('fecha_programada');
            $table->string('estado')->nullable()->change();
        });

        DB::table('programados')->where('estado', 'asiste')->update(['estado' => 'Realizado']);
        DB::table('programados')->where('estado', 'no_asiste')->update(['estado' => 'No asistió']);
        DB::table('programados')->where('estado', 'reagendado')->update(['estado' => 'Pendiente']);
        DB::table('programados')->whereNull('estado')->update(['estado' => 'Pendiente']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('programados')->whereNotNull('tipo_control')->exists()) {
            throw new RuntimeException('Cannot remove scheduled control types while values are stored.');
        }

        $hasUnsupportedStates = DB::table('programados')
            ->whereNotNull('estado')
            ->whereNotIn('estado', ['Pendiente', 'Realizado', 'No asistió'])
            ->exists();

        if ($hasUnsupportedStates) {
            throw new RuntimeException('Cannot restore the programados enum while unsupported states are stored.');
        }

        DB::table('programados')->where('estado', 'Pendiente')->update(['estado' => 'reagendado']);
        DB::table('programados')->where('estado', 'Realizado')->update(['estado' => 'asiste']);
        DB::table('programados')->where('estado', 'No asistió')->update(['estado' => 'no_asiste']);

        Schema::table('programados', function (Blueprint $table) {
            $table->dropColumn('tipo_control');
            $table->enum('estado', ['asiste', 'no_asiste', 'reagendado'])->nullable()->change();
        });
    }
};

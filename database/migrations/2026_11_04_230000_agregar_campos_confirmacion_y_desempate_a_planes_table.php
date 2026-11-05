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
        Schema::table('planes', function (Blueprint $table) {
            $table->foreignId('propuesta_fecha_id')
                ->nullable()
                ->after('estado')
                ->constrained('opciones')
                ->nullOnDelete();

            $table->foreignId('propuesta_lugar_id')
                ->nullable()
                ->after('propuesta_fecha_id')
                ->constrained('opciones')
                ->nullOnDelete();

            $table->text('motivo_desempate')
                ->nullable()
                ->after('propuesta_lugar_id');

            $table->timestamp('fecha_confirmacion')
                ->nullable()
                ->after('motivo_desempate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planes', function (Blueprint $table) {
            $table->dropForeign(['propuesta_fecha_id']);
            $table->dropForeign(['propuesta_lugar_id']);
            $table->dropColumn([
                'propuesta_fecha_id',
                'propuesta_lugar_id',
                'motivo_desempate',
                'fecha_confirmacion',
            ]);
        });
    }
};

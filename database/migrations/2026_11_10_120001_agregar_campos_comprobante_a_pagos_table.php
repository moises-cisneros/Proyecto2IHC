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
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('comprobante_path')->nullable()->after('monto');
            $table->decimal('monto_detectado', 10, 2)->nullable()->after('comprobante_path');
            $table->string('banco_detectado')->nullable()->after('monto_detectado');
            $table->string('estado_escaneo')->default('sin_comprobante')->after('banco_detectado');
            $table->decimal('confianza_escaneo', 5, 2)->nullable()->after('estado_escaneo');
            $table->json('detalles_escaneo')->nullable()->after('confianza_escaneo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn([
                'comprobante_path',
                'monto_detectado',
                'banco_detectado',
                'estado_escaneo',
                'confianza_escaneo',
                'detalles_escaneo',
            ]);
        });
    }
};

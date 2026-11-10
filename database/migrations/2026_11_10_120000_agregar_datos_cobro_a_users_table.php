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
        Schema::table('users', function (Blueprint $table) {
            $table->string('alias_pago')->nullable()->after('email');
            $table->string('cbu_pago')->nullable()->after('alias_pago');
            $table->string('qr_pago_path')->nullable()->after('cbu_pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['alias_pago', 'cbu_pago', 'qr_pago_path']);
        });
    }
};

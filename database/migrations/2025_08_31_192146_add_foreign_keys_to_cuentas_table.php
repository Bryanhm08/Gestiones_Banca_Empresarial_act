<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cuentas', function (Blueprint $table) {
            $table->foreign('cliente_id')
                ->references('id')
                ->on('clientes')
                ->onDelete('cascade');

            $table->foreign('tipo_cuenta_id')
                ->references('id')
                ->on('tipos_cuenta')
                ->onDelete('cascade');

            $table->foreign('asesor_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuentas', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropForeign(['tipo_cuenta_id']);
            $table->dropForeign(['asesor_id']);
        });
    }
};

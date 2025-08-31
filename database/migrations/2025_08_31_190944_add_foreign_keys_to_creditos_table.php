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
        Schema::table('creditos', function (Blueprint $table) {
            $table->foreign('tipo_credito_id')
                ->references('id')
                ->on('tipos_credito')
                ->onDelete('cascade');

            $table->foreign('garantia_id')
                ->references('id')
                ->on('garantias')
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
        Schema::table('creditos', function (Blueprint $table) {
            $table->dropForeign(['tipo_credito_id']);
            $table->dropForeign(['garantia_id']);
            $table->dropForeign(['asesor_id']);
        });
    }
};

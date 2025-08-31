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
        Schema::table('estado_credito', function (Blueprint $table) {
            $table->foreign('credito_id')
                ->references('id')
                ->on('creditos')
                ->onDelete('cascade');

            $table->foreign('estado_id')
                ->references('id')
                ->on('estados_credito')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estado_credito', function (Blueprint $table) {
            $table->dropForeign(['credito_id']);
            $table->dropForeign(['estado_id']);
        });
    }
};

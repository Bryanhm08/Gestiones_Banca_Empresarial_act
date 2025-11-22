<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Evita error si ya existe la columna (por ejemplo migracion re-ejecutada)
        if (! Schema::hasColumn('creditos', 'tasa_interes')) {
            Schema::table('creditos', function (Blueprint $table) {
                // Para máxima compatibilidad con distintos engines, no forzamos `after()`
                // (si realmente quieres controlar la posición, puedes usar ->after('plazo') en MySQL).
                $table->decimal('tasa_interes', 5, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('creditos', 'tasa_interes')) {
            Schema::table('creditos', function (Blueprint $table) {
                $table->dropColumn('tasa_interes');
            });
        }
    }
};

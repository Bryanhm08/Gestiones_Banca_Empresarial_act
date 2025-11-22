<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('creditos', function (Blueprint $table) {
            // tasa en porcentaje, p.ej. 12.50 -> decimal(5,2)
            $table->decimal('tasa_interes', 5, 2)->default(0.00)->after('plazo');
        });
    }

    public function down(): void
    {
        Schema::table('creditos', function (Blueprint $table) {
            $table->dropColumn('tasa_interes');
        });
    }
};

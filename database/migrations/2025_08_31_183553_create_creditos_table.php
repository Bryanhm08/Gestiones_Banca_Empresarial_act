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
        Schema::create('creditos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->unsignedBigInteger('tipo_credito_id')->nullable();
            $table->unsignedBigInteger('garantia_id')->nullable();
            $table->decimal('monto', 15, 2);
            $table->integer('plazo');
            $table->date('fecha_concesion');
            $table->date('fecha_vencimiento');
            $table->foreignId('asesor_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creditos');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asesor_id');              // dueño del evento (quién agenda)
            $table->unsignedBigInteger('cliente_id')->nullable(); // cliente asociado (opcional)
            $table->string('title');                               // Asunto de la reunión
            $table->text('description')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();

            $table->foreign('asesor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('cliente_id')->references('id')->on('clientes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};

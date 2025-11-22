<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('liberaciones', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $t->string('nombre')->nullable();
            $t->json('columns');
            $t->json('rows')->nullable();
            $t->foreignId('created_by')->constrained('users');
            $t->foreignId('updated_by')->constrained('users');
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('liberaciones');
    }
};

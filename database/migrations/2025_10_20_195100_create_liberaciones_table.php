<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('liberaciones', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $t->string('nombre')->nullable(); // opcional: “Liberaciones OCT-2025” etc.
            $t->json('columns');              // [{id:'correlativo',label:'Correlativo'}, ...]
            $t->json('rows')->nullable();     // [{id:'r1', status:'pendiente', values:{correlativo:'1',cliente:'...',...}}, ...]
            $t->foreignId('created_by')->constrained('users');
            $t->foreignId('updated_by')->constrained('users');
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('liberaciones');
    }
};

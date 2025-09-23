<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('estados_credito', 'orden')) {
            Schema::table('estados_credito', function (Blueprint $table) {
                $table->unsignedTinyInteger('orden')->nullable()->after('id');
                $table->index('orden');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('estados_credito', 'orden')) {
            Schema::table('estados_credito', function (Blueprint $table) {
                $table->dropIndex(['orden']);
                $table->dropColumn('orden');
            });
        }
    }
};

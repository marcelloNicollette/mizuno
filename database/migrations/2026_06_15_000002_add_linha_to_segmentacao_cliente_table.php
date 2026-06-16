<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('segmentacao_cliente', function (Blueprint $table) {
            $table->string('linha')->nullable()->after('produtos_segmentos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('segmentacao_cliente', function (Blueprint $table) {
            $table->dropColumn('linha');
        });
    }
};

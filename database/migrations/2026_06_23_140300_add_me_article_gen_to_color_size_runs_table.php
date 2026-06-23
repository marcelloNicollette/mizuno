<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('color_size_runs', function (Blueprint $table) {
            $table->string('me_article_gen', 20)->nullable()->after('article_value');
        });
    }

    public function down(): void
    {
        Schema::table('color_size_runs', function (Blueprint $table) {
            $table->dropColumn('me_article_gen');
        });
    }
};

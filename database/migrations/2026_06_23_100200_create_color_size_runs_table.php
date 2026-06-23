<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('color_size_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('color_id')->unique()->constrained('colors')->cascadeOnDelete();
            $table->foreignId('size_run_id')->constrained('size_runs')->cascadeOnDelete();
            $table->string('article_label', 60)->nullable();
            $table->string('article_value', 60)->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('color_size_runs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('size_run_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('size_run_id')->constrained('size_runs')->cascadeOnDelete();
            $table->string('left_value', 30);
            $table->string('right_value', 30);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('size_run_items');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('size_runs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('title', 120)->default('Size Run');
            $table->string('size_label_left', 60)->default('BR SIZE');
            $table->string('size_label_right', 60)->default('US SIZE');
            $table->string('note', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('size_runs');
    }
};

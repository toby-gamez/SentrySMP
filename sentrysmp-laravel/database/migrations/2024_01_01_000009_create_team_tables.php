<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('team_ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('hex_color', 20)->default('');
            $table->timestamps();
        });

        Schema::create('team_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('minecraft_name', 100);
            $table->foreignId('team_rank_id')->nullable()->constrained('team_ranks')->nullOnDelete();
            $table->string('skin_url', 500)->nullable();
            $table->integer('sort_order')->default(0);
            $table->uuid('team_category_id');
            $table->timestamps();
            $table->foreign('team_category_id')->references('id')->on('team_categories')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('team_categories');
        Schema::dropIfExists('team_ranks');
    }
};

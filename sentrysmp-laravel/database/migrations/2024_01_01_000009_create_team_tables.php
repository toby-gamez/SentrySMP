<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teamranks', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name', 100);
            $table->string('HexColor', 20)->default('');
        });

        Schema::create('teamcategories', function (Blueprint $table) {
            $table->uuid('Id')->primary();
            $table->string('Name', 100);
            $table->integer('SortOrder')->default(0);
        });

        Schema::create('teammembers', function (Blueprint $table) {
            $table->uuid('Id')->primary();
            $table->string('MinecraftName', 100);
            $table->unsignedBigInteger('TeamRankId')->nullable();
            $table->string('SkinUrl', 500)->nullable();
            $table->integer('SortOrder')->default(0);
            $table->uuid('TeamCategoryId');
            $table->foreign('TeamRankId')->references('Id')->on('teamranks')->nullOnDelete();
            $table->foreign('TeamCategoryId')->references('Id')->on('teamcategories')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teammembers');
        Schema::dropIfExists('teamcategories');
        Schema::dropIfExists('teamranks');
    }
};

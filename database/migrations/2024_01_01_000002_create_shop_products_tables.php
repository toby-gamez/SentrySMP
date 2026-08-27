<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('keys', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description', 500)->default('');
            $table->float('price');
            $table->foreignId('server_id')->constrained('servers')->cascadeOnDelete();
            $table->integer('sale')->default(0);
            $table->string('image', 255)->nullable();
            $table->integer('global_max_order')->nullable();
            $table->timestamps();
        });

        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description', 500)->default('');
            $table->float('price');
            $table->integer('sale')->default(0);
            $table->string('image', 255)->nullable();
            $table->integer('global_max_order')->nullable();
            $table->timestamps();
        });

        Schema::create('bundles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description', 500)->default('');
            $table->float('price');
            $table->foreignId('server_id')->constrained('servers')->cascadeOnDelete();
            $table->integer('sale')->default(0);
            $table->string('image', 255)->nullable();
            $table->integer('global_max_order')->nullable();
            $table->timestamps();
        });

        Schema::create('coins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description', 500)->nullable();
            $table->float('price');
            $table->foreignId('server_id')->constrained('servers')->cascadeOnDelete();
            $table->integer('sale')->default(0);
            $table->string('image', 255)->nullable();
            $table->integer('global_max_order')->nullable();
            $table->timestamps();
        });

        Schema::create('battle_passes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description', 500)->default('');
            $table->float('price');
            $table->foreignId('server_id')->constrained('servers')->cascadeOnDelete();
            $table->integer('sale')->default(0);
            $table->string('image', 255)->nullable();
            $table->integer('global_max_order')->nullable();
            $table->timestamps();
        });

        Schema::create('others', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description', 500)->nullable();
            $table->float('price');
            $table->foreignId('server_id')->constrained('servers')->cascadeOnDelete();
            $table->integer('sale')->default(0);
            $table->string('image', 255)->nullable();
            $table->integer('global_max_order')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('others');
        Schema::dropIfExists('battle_passes');
        Schema::dropIfExists('coins');
        Schema::dropIfExists('bundles');
        Schema::dropIfExists('ranks');
        Schema::dropIfExists('keys');
    }
};

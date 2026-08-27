<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('active_players', function (Blueprint $table) {
            $table->id();
            $table->string('username', 64);
            $table->unsignedBigInteger('coins')->default(0);
            $table->decimal('money', 15, 2)->default(0);
            $table->string('rank', 64)->nullable();
            $table->unsignedBigInteger('play_time_seconds')->default(0);
            $table->unsignedBigInteger('play_time_ticks')->default(0);
            $table->unsignedInteger('deaths')->default(0);
            $table->unsignedInteger('player_kills')->default(0);
            $table->unsignedInteger('mobs_killed')->default(0);
            $table->unsignedInteger('blocks_travelled')->default(0);
            $table->string('error', 512)->nullable();
            $table->timestamp('reported_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('active_players');
    }
};

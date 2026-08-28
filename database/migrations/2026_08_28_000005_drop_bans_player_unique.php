<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A player can have multiple ban records (ban history). The unique
        // constraint on player was added when bans were assumed to be one-per-player,
        // but the game server sends full history including multiple entries per player.
        $indexExists = collect(DB::select("SHOW INDEX FROM bans WHERE Key_name = 'bans_player_unique'"))->isNotEmpty();
        if ($indexExists) {
            Schema::table('bans', function (Blueprint $table) {
                $table->dropUnique(['player']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('bans', function (Blueprint $table) {
            $table->unique('player');
        });
    }
};

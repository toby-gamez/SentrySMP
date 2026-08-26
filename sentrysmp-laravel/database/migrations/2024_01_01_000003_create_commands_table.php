<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('commands', function (Blueprint $table) {
            $table->id();
            $table->text('command_text');
            $table->string('type', 30); // Key, Coin, Bundle, Rank, BattlePass, Other
            $table->unsignedInteger('type_id');
            $table->timestamps();
            $table->index(['type', 'type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commands');
    }
};

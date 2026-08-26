<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('command_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->string('player_name', 100);
            $table->text('command_text');
            $table->enum('status', ['pending', 'delivered', 'executed', 'failed'])->default('pending');
            $table->timestamps();

            $table->index('status');
            $table->index('player_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('command_queue');
    }
};

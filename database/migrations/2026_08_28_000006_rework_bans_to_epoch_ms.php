<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bans', function (Blueprint $table) {
            $table->dropColumn(['banned_ago', 'expires_at']);
        });

        Schema::table('bans', function (Blueprint $table) {
            // Unix epoch milliseconds; -1 means permanent for `until`
            $table->bigInteger('time')->default(0)->after('active');
            $table->bigInteger('until')->default(-1)->after('time');
        });
    }

    public function down(): void
    {
        Schema::table('bans', function (Blueprint $table) {
            $table->dropColumn(['time', 'until']);
        });

        Schema::table('bans', function (Blueprint $table) {
            $table->string('expires_at', 64)->nullable()->after('reason');
            $table->string('banned_ago', 64)->nullable()->after('expires_at');
        });
    }
};

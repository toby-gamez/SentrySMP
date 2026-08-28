<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bans', function (Blueprint $table) {
            // Replace timestamp expires_at with a plain string (e.g. "7 days", "permanent")
            $table->dropColumn('expires_at');
        });

        Schema::table('bans', function (Blueprint $table) {
            $table->string('expires_at', 64)->nullable()->after('reason');
            $table->string('banned_ago', 64)->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('bans', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'banned_ago']);
        });

        Schema::table('bans', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('reason');
        });
    }
};

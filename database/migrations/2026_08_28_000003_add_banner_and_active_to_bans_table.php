<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bans', function (Blueprint $table) {
            $table->string('banner', 64)->nullable()->after('uuid');
            $table->boolean('active')->default(true)->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('bans', function (Blueprint $table) {
            $table->dropColumn(['banner', 'active']);
        });
    }
};

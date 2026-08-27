<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // G3: prevent duplicate transaction processing.
        // First remove any duplicate rows, keeping only the earliest record per provider_transaction_id.
        DB::statement('
            DELETE pt FROM payment_transactions pt
            INNER JOIN payment_transactions pt2
                ON pt.provider_transaction_id = pt2.provider_transaction_id
                AND pt.id > pt2.id
        ');

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->unique('provider_transaction_id');
        });

        // G7: track when commands were dispatched to the game server so stale
        //     "delivered" rows can be detected and re-queued
        Schema::table('command_queue', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropUnique(['provider_transaction_id']);
        });

        Schema::table('command_queue', function (Blueprint $table) {
            $table->dropColumn('delivered_at');
        });
    }
};

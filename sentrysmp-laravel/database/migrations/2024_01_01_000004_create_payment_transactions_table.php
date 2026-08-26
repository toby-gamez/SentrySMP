<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('provider', 50);
            $table->string('provider_transaction_id', 200)->default('');
            $table->decimal('amount', 18, 2)->default(0);
            $table->string('currency', 10)->default('EUR');
            $table->string('minecraft_username', 100)->default('');
            $table->text('items_json')->nullable();
            $table->string('status', 100)->default('');
            $table->text('raw_response')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('minecraft_username');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('description', 500)->default('');
            $table->dateTime('start_date');
            $table->dateTime('expiration_date');
            $table->integer('max_uses')->nullable();
            $table->integer('current_uses')->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->string('scope', 20)->default('All'); // All, Category, Item
            $table->string('scope_category', 30)->nullable();
            $table->integer('scope_item_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('voucher_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->string('minecraft_username', 100);
            $table->timestamp('used_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_usages');
        Schema::dropIfExists('vouchers');
    }
};

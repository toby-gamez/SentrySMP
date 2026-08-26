<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_purchase_records', function (Blueprint $table) {
            $table->id();
            $table->string('minecraft_username', 100);
            $table->string('product_type', 50);
            $table->unsignedInteger('product_id');
            $table->integer('total_quantity_purchased')->default(0);
            $table->timestamp('last_purchase_date')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['minecraft_username', 'product_type', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_purchase_records');
    }
};

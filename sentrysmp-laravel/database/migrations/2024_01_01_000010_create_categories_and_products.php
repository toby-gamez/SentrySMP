<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('others');
        Schema::dropIfExists('battle_passes');
        Schema::dropIfExists('coins');
        Schema::dropIfExists('bundles');
        Schema::dropIfExists('ranks');
        Schema::dropIfExists('keys');

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('color', 7)->default('#888888');
            $table->string('image', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description', 500)->default('');
            $table->decimal('price', 8, 2);
            $table->integer('sale')->default(0);
            $table->string('image', 255)->nullable();
            $table->integer('global_max_order')->nullable();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('commands', function (Blueprint $table) {
            $table->dropColumn(['type', 'type_id']);
            $table->foreignId('product_id')->after('id')->constrained('products')->cascadeOnDelete();
        });

        Schema::table('user_purchase_records', function (Blueprint $table) {
            $table->dropIndex('upr_username_type_id_idx');
            $table->dropColumn('product_type');
            $table->index(['minecraft_username', 'product_id'], 'upr_username_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('user_purchase_records', function (Blueprint $table) {
            $table->dropIndex('upr_username_id_idx');
            $table->string('product_type', 50)->after('minecraft_username');
            $table->index(['minecraft_username', 'product_type', 'product_id'], 'upr_username_type_id_idx');
        });

        Schema::table('commands', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->string('type', 30)->after('id');
            $table->unsignedInteger('type_id')->after('type');
            $table->index(['type', 'type_id']);
        });

        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};

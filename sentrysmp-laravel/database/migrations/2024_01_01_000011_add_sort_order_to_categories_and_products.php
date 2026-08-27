<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('image');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('category_id');
        });

        DB::statement('UPDATE categories SET sort_order = id');
        DB::statement('UPDATE products SET sort_order = id');
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};

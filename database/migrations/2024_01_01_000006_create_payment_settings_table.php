<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enable_payments')->default(true);
            $table->boolean('disable_stripe')->default(false);
            $table->boolean('disable_paypal')->default(false);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Seed a default row
        \DB::table('payment_settings')->insert([
            'enable_payments' => true,
            'disable_stripe'  => false,
            'disable_paypal'  => false,
            'updated_at'      => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};

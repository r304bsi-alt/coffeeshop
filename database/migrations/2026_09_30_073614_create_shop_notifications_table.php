<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // order_paid_for_kitchen, order_ready_for_cashier
            $table->string('title');
            $table->text('message');
            $table->string('target_role'); // dapur, kasir, owner
            $table->json('data')->nullable(); // order_id, table_number, order_number, etc.
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_notifications');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name');
            $table->string('table_number')->nullable();
            $table->enum('order_type', ['dine_in', 'take_away'])->default('dine_in');
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->enum('payment_method', ['cash', 'midtrans_qris'])->default('cash');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->enum('order_status', ['pending_payment', 'in_kitchen', 'ready', 'completed', 'cancelled'])->default('pending_payment');
            $table->string('payment_token')->nullable(); // QR code token for cash or Midtrans
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('kitchen_done_at')->nullable();
            $table->timestamp('cashier_called_at')->nullable();
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

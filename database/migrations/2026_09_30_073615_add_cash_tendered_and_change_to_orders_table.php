<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('cash_tendered', 14, 2)->nullable()->after('total_amount');
            $table->decimal('change_amount', 14, 2)->nullable()->after('cash_tendered');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['cash_tendered', 'change_amount']);
        });
    }
};

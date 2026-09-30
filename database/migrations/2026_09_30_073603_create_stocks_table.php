<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->enum('location', ['gudang', 'toko']);
            $table->decimal('quantity', 14, 2)->default(0); // precision decimal for gram/ml
            $table->timestamps();

            $table->unique(['ingredient_id', 'location']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};

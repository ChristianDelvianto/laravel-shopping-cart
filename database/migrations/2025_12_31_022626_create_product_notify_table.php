<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_notify', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_version')->default(1);
            $table->foreignId('product_id')->constrained('products', 'id')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->timestamps();

            // Indexes
            $table->unique(['product_id', 'user_id', 'product_version']);
            $table->index(['user_id', 'product_version']);
            $table->index('created_at'); // First come, first notify
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_notify');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique()->index(); // e.g., "WELCOME10"
            $table->decimal('percentage', 5, 2); // e.g., 15.00 (max 999.99%)
            $table->unsignedInteger('max_usage')->nullable(); // global usage limit
            $table->unsignedInteger('user_limit')->default(1); // how many times per user
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
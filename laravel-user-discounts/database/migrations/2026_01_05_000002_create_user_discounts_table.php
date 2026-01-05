<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('discount_id')->constrained('discounts')->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('revoked_at')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();

            // Ensure a user can't be assigned the same discount twice (unless revoked and reassigned)
            $table->unique(['user_id', 'discount_id', 'revoked_at']); // revoked_at null means active
            $table->index(['user_id', 'discount_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_discounts');
    }
};
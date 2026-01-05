<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('discount_id')->constrained('discounts')->onDelete('cascade');
            $table->enum('action', ['assign', 'revoke', 'apply']);
            $table->unsignedInteger('old_usage')->default(0);
            $table->unsignedInteger('new_usage');
            $table->timestamp('applied_at')->useCurrent();
            $table->ipAddress('ip_address')->nullable(); // Security audit trail
            $table->timestamps();

            $table->index(['user_id', 'discount_id']);
            $table->index('action');
            $table->index('applied_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_audits');
    }
};
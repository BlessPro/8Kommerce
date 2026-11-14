<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promoter_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('public_code', 16)->unique();
            $table->string('name', 120);
            $table->string('phone', 30)->unique();
            $table->string('city', 120)->nullable();
            $table->jsonb('channels')->nullable();
            $table->text('note')->nullable();
            $table->string('status', 24)->default('active')->index();
            $table->unsignedBigInteger('visits_count')->default(0);
            $table->unsignedBigInteger('conversions_count')->default(0);
            $table->decimal('conversion_amount', 14, 2)->default(0);
            $table->timestamp('last_visited_at')->nullable();
            $table->timestamps();
        });

        Schema::create('promoter_conversions', function (Blueprint $table) {
            $table->id();
            $table->uuid('promoter_profile_id');
            $table->uuid('payment_id')->unique();
            $table->uuid('order_id')->nullable();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 10)->default('GHS');
            $table->timestamp('converted_at');
            $table->timestamps();

            $table->foreign('promoter_profile_id')->references('id')->on('promoter_profiles')->cascadeOnDelete();
            $table->foreign('payment_id')->references('id')->on('payments')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            $table->index(['promoter_profile_id', 'converted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promoter_conversions');
        Schema::dropIfExists('promoter_profiles');
    }
};

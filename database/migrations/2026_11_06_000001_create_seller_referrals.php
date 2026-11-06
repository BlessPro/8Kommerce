<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('code', 12)->unique();
            $table->unsignedBigInteger('visits_count')->default(0);
            $table->timestamp('last_visited_at')->nullable();
            $table->timestamps();
        });

        Schema::create('seller_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_link_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('status', 24)->default('joined')->index();
            $table->timestamp('registered_at');
            $table->timestamps();

            $table->index(['referrer_id', 'registered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_referrals');
        Schema::dropIfExists('referral_links');
    }
};

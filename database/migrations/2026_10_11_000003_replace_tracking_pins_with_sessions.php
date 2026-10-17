<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_tracking_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('phone', 30)->index();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at')->index();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('customer_tracking_profiles');
    }

    public function down(): void
    {
        Schema::create('customer_tracking_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 30)->unique();
            $table->string('pin_hash');
            $table->text('pin_secret');
            $table->unsignedSmallInteger('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable()->index();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamp('pin_rotated_at')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('customer_tracking_sessions');
    }
};

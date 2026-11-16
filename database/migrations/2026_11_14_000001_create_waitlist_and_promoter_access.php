<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waitlist_entries', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 30)->unique();
            $table->string('status', 24)->default('waiting')->index();
            $table->string('source', 80)->nullable()->index();
            $table->timestamp('joined_at');
            $table->timestamps();
        });

        Schema::table('promoter_profiles', function (Blueprint $table) {
            $table->string('access_pin_hash')->nullable()->after('phone');
            $table->timestamp('last_login_at')->nullable()->after('last_visited_at');
        });

        Schema::create('promoter_visits', function (Blueprint $table) {
            $table->id();
            $table->uuid('promoter_profile_id');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('destination', 24)->default('marketplace')->index();
            $table->string('visitor_hash', 64)->index();
            $table->string('referrer_host', 180)->nullable();
            $table->timestamp('visited_at')->index();
            $table->timestamps();

            $table->foreign('promoter_profile_id')->references('id')->on('promoter_profiles')->cascadeOnDelete();
            $table->index(['promoter_profile_id', 'visited_at']);
            $table->index(['promoter_profile_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promoter_visits');

        Schema::table('promoter_profiles', function (Blueprint $table) {
            $table->dropColumn(['access_pin_hash', 'last_login_at']);
        });

        Schema::dropIfExists('waitlist_entries');
    }
};

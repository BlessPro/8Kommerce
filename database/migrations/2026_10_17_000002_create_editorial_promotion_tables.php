<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('purpose', 40);
            $table->text('seller_note')->nullable();
            $table->string('status', 32)->default('submitted')->index();
            $table->string('channel', 32)->nullable()->index();
            $table->string('tracking_code', 24)->unique();
            $table->text('admin_notes')->nullable();
            $table->string('published_url', 2048)->nullable();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        Schema::create('editorial_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 40)->default('seller_spotlight')->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt');
            $table->longText('body');
            $table->string('cover_image_path')->nullable();
            $table->json('product_ids')->nullable();
            $table->string('status', 24)->default('draft')->index();
            $table->string('seo_title', 60)->nullable();
            $table->string('seo_description', 160)->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('featured_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('placement', 40)->default('marketplace_home')->index();
            $table->string('source', 24)->default('editorial');
            $table->string('label', 80)->nullable();
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at')->index();
            $table->timestamps();
        });

        Schema::create('seller_recognitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('article_id')->nullable()->constrained('editorial_articles')->nullOnDelete();
            $table->string('type', 40)->default('seller_of_month');
            $table->date('period_start');
            $table->string('title')->default('Seller of the Month');
            $table->text('summary')->nullable();
            $table->timestamps();

            $table->unique(['type', 'period_start']);
        });

        Schema::table('order_feedbacks', function (Blueprint $table) {
            $table->text('seller_reply')->nullable()->after('note');
            $table->timestamp('seller_replied_at')->nullable()->after('seller_reply');
            $table->unique('order_feedback_token_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_feedbacks', function (Blueprint $table) {
            $table->dropUnique(['order_feedback_token_id']);
            $table->dropColumn(['seller_reply', 'seller_replied_at']);
        });
        Schema::dropIfExists('seller_recognitions');
        Schema::dropIfExists('featured_placements');
        Schema::dropIfExists('editorial_articles');
        Schema::dropIfExists('promotion_submissions');
    }
};

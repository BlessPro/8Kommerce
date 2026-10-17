<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan_type', 32)->index();
            $table->string('provider', 24)->default('paystack');
            $table->string('provider_plan_code')->nullable()->index();
            $table->string('provider_subscription_code')->nullable()->unique();
            $table->string('provider_customer_code')->nullable()->index();
            $table->text('provider_email_token')->nullable();
            $table->string('checkout_reference')->unique();
            $table->string('billing_email');
            $table->string('status', 32)->default('pending')->index();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('GHS');
            $table->string('interval', 16)->default('monthly');
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable()->index();
            $table->timestamp('next_payment_at')->nullable();
            $table->timestamp('grace_ends_at')->nullable()->index();
            $table->boolean('cancel_at_period_end')->default(false);
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('last_payment_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('last_event_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 24)->default('paystack');
            $table->string('provider_invoice_code')->nullable()->unique();
            $table->string('reference')->nullable()->unique();
            $table->string('status', 24)->default('pending')->index();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('GHS');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('subscriptions');
    }
};

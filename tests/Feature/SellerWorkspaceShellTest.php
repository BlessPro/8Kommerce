<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SellerWorkspaceShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_home_uses_task_navigation_and_priority_dashboard(): void
    {
        $user = User::factory()->create([
            'phone' => '+233201234567',
            'pin_hash' => Hash::make('2486'),
        ]);

        SellerProfile::create([
            'user_id' => $user->id,
            'business_name' => 'North Star Shop',
            'phone' => '+233201234567',
            'public_slug' => 'north-star-shop',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('What needs your attention today')
            ->assertSee('Quick menu')
            ->assertSee('North Star Shop')
            ->assertSee('+233201234567')
            ->assertSee('More')
            ->assertDontSee($user->email);
    }

    public function test_activity_page_leads_with_orders_that_need_a_decision(): void
    {
        $user = User::factory()->create([
            'phone' => '+233201234568',
            'pin_hash' => Hash::make('2486'),
        ]);

        SellerProfile::create([
            'user_id' => $user->id,
            'business_name' => 'Activity Shop',
            'phone' => '+233201234568',
            'public_slug' => 'activity-shop',
        ]);

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Activity and order decisions')
            ->assertSee('Orders awaiting your decision')
            ->assertSee('No paid orders need a decision')
            ->assertSee('Recent updates');
    }

    public function test_catalogue_workspace_uses_the_shared_product_experience(): void
    {
        $user = $this->createSeller('Catalogue Shop', '+233201234569', 'catalogue-shop');

        $this->actingAs($user)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('Manage what customers see')
            ->assertSee('Product revenue')
            ->assertSee('Add products');

        $this->actingAs($user)
            ->get(route('products.create'))
            ->assertOk()
            ->assertSee('A stronger product listing')
            ->assertSee('Your progress is protected');

        $this->actingAs($user)
            ->get(route('product-categories.index'))
            ->assertOk()
            ->assertSee('Catalogue organisation')
            ->assertSee('Saved filters');
    }

    public function test_sales_workspace_exposes_order_filters_and_customer_directory(): void
    {
        $user = $this->createSeller('Sales Shop', '+233201234570', 'sales-shop');

        $this->actingAs($user)
            ->get(route('products.orders'))
            ->assertOk()
            ->assertSee('Fulfilment worklist')
            ->assertSee('Needs action')
            ->assertSee('No orders in this view');

        $this->actingAs($user)
            ->get(route('customers.index'))
            ->assertOk()
            ->assertSee('Customer directory')
            ->assertSee('Customer segments')
            ->assertSee('No customers found');
    }

    public function test_money_workspace_uses_consistent_payment_invoice_and_coupon_views(): void
    {
        $user = $this->createSeller('Money Shop', '+233201234571', 'money-shop');
        $invoice = Invoice::create([
            'user_id' => $user->id,
            'token' => 'money-shop-invoice-token',
            'title' => 'Custom order deposit',
            'description' => 'Deposit for a customer order.',
            'total_amount' => '250.00',
            'paid_total' => '50.00',
            'payment_mode' => Invoice::MODE_PARTIAL,
            'deposit_amount' => '50.00',
            'status' => Invoice::STATUS_PARTIAL,
            'customer_name' => 'Ama Customer',
        ]);

        $this->actingAs($user)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('Money activity')
            ->assertSee('Pending payments')
            ->assertSee('No transactions found');

        $this->actingAs($user)
            ->get(route('invoices.index'))
            ->assertOk()
            ->assertSee('Payment links')
            ->assertSee('Latest invoice')
            ->assertSee('Custom order deposit');

        $this->actingAs($user)
            ->get(route('invoices.show', $invoice))
            ->assertOk()
            ->assertSee('Customer payment link')
            ->assertSee('Link performance');

        $this->actingAs($user)
            ->get(route('invoices.create'))
            ->assertOk()
            ->assertSee('Share and get paid')
            ->assertSee('Draft protection is on');

        $this->actingAs($user)
            ->get(route('coupons.index'))
            ->assertOk()
            ->assertSee('Discount library')
            ->assertSee('No coupons yet');
    }

    public function test_growth_workspace_connects_goals_insights_and_promotions(): void
    {
        $user = $this->createSeller('Growth Shop', '+233201234572', 'growth-shop');

        $this->actingAs($user)
            ->get(route('goals.index'))
            ->assertOk()
            ->assertSee('Goals and performance')
            ->assertSee('Recommended focus')
            ->assertSee('Advanced insights');

        $this->actingAs($user)
            ->get(route('insights.index'))
            ->assertOk()
            ->assertSee('Advanced insights')
            ->assertSee('Reporting period');

        $this->actingAs($user)
            ->get(route('promotions.index'))
            ->assertOk()
            ->assertSee('Promote your products')
            ->assertSee('Nominations remaining')
            ->assertSee('No nominations yet');
    }

    public function test_storefront_studio_keeps_design_controls_and_phone_preview_together(): void
    {
        $user = $this->createSeller('Studio Shop', '+233201234573', 'studio-shop');

        $this->actingAs($user)
            ->get(route('public.preview'))
            ->assertOk()
            ->assertSee('Storefront studio')
            ->assertSee('Design controls')
            ->assertSee('Phone preview')
            ->assertSee('iPhone 14')
            ->assertSee('Save and publish');
    }

    public function test_account_and_plan_workspace_explain_setup_and_billing_status(): void
    {
        $user = $this->createSeller('Account Shop', '+233201234574', 'account-shop');

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Store settings')
            ->assertSee('Seller profile and payout')
            ->assertSee('Settings checklist')
            ->assertSee('Delivery and pickup')
            ->assertSee('Open security settings');

        $this->actingAs($user)
            ->get(route('billing.show'))
            ->assertOk()
            ->assertSee('Plan and billing')
            ->assertSee('Current access')
            ->assertSee('Switch plan');
    }

    private function createSeller(string $businessName, string $phone, string $slug): User
    {
        $user = User::factory()->create([
            'phone' => $phone,
            'pin_hash' => Hash::make('2486'),
        ]);

        SellerProfile::create([
            'user_id' => $user->id,
            'business_name' => $businessName,
            'phone' => $phone,
            'public_slug' => $slug,
        ]);

        return $user;
    }
}

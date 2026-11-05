<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class DesignSystemComponentsTest extends TestCase
{
    public function test_core_design_system_components_render_with_semantic_classes(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.button variant="danger" icon="trash">Delete</x-ui.button>
            <x-ui.badge tone="success" icon="check">Paid</x-ui.badge>
            <x-ui.alert tone="warning" title="Pending">Still processing.</x-ui.alert>
            <x-ui.empty-state title="No products" description="Add your first product." />
            <x-ui.stat-card label="Orders" value="12" icon="package" />
        BLADE);

        $this->assertStringContainsString('lp-button-danger', $html);
        $this->assertStringContainsString('icon-[tabler--trash]', $html);
        $this->assertStringContainsString('lp-badge-success', $html);
        $this->assertStringContainsString('lp-alert-warning', $html);
        $this->assertStringContainsString('lp-empty-state', $html);
        $this->assertStringContainsString('lp-stat-card', $html);
    }

    public function test_form_field_exposes_help_and_validation_text(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.field label="Business name" name="business_name" help="Shown on your store." error="Enter a business name." required>
                <x-text-input id="business_name" name="business_name" aria-invalid="true" />
            </x-ui.field>
        BLADE);

        $this->assertStringContainsString('Business name', $html);
        $this->assertStringContainsString('required', $html);
        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('Enter a business name.', $html);
        $this->assertStringContainsString('lp-form-control', $html);
    }
}

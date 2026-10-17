<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_pages_use_the_branded_not_found_experience(): void
    {
        $this->get('/a-page-that-does-not-exist')
            ->assertNotFound()
            ->assertSee('This page is not here.')
            ->assertSee('Browse stores');
    }

    public function test_common_error_views_can_render_without_frontend_assets(): void
    {
        foreach ([403, 419, 429, 500, 503] as $status) {
            $html = view("errors.{$status}")->render();

            $this->assertStringContainsString("Error {$status}", $html);
            $this->assertStringContainsString('8Kommerce', $html);
        }
    }

    public function test_shared_page_loader_is_present_on_public_and_auth_pages(): void
    {
        $this->get('/')->assertOk()->assertSee('data-page-loader', false);
        $this->get(route('login'))->assertOk()->assertSee('data-page-loader', false);
    }
}

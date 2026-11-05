<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_page_reports_healthy_dependencies(): void
    {
        $this->get('/up')
            ->assertOk()
            ->assertSee('Everything is running normally.')
            ->assertSee('All systems operational')
            ->assertSee('Website &amp; marketplace', false)
            ->assertSee('What each service covers')
            ->assertSee('Service questions')
            ->assertSee('Track an order')
            ->assertSee('Database')
            ->assertSee('Queue');
    }

    public function test_customer_friendly_status_route_uses_the_same_health_report(): void
    {
        $this->get('/status')
            ->assertOk()
            ->assertSee('Orders and checkout')
            ->assertSee('Payment processing')
            ->assertSee('SMS notifications')
            ->assertSee('My payment succeeded but my order still says pending. What should I do?');
    }

    public function test_status_endpoint_can_return_machine_readable_json(): void
    {
        $this->getJson('/up')
            ->assertOk()
            ->assertJsonPath('status', 'operational')
            ->assertJsonCount(4, 'checks')
            ->assertJsonCount(6, 'services')
            ->assertJsonStructure([
                'status',
                'checked_at',
                'response_time_ms',
                'version',
                'checks' => [['name', 'description', 'status', 'response_time_ms']],
                'services' => [['name', 'description', 'status']],
                'incident' => ['active', 'severity', 'title', 'message', 'updated_at'],
            ]);
    }

    public function test_active_incident_is_shown_without_breaking_the_status_page(): void
    {
        config([
            'status.incident.active' => true,
            'status.incident.severity' => 'maintenance',
            'status.incident.title' => 'Checkout maintenance',
            'status.incident.message' => 'Checkout may be briefly unavailable.',
            'status.incident.updated_at' => '2026-11-05 12:00 UTC',
        ]);

        $this->get('/status')
            ->assertOk()
            ->assertSee('Scheduled maintenance')
            ->assertSee('Checkout maintenance')
            ->assertSee('Checkout may be briefly unavailable.');

        $this->getJson('/status')
            ->assertOk()
            ->assertJsonPath('status', 'degraded')
            ->assertJsonPath('incident.active', true);
    }

    public function test_lightweight_liveness_endpoint_remains_available(): void
    {
        $this->get('/live')->assertOk();
    }

    public function test_dependency_failure_returns_a_safe_degraded_response(): void
    {
        config([
            'queue.default' => 'health-test',
            'queue.connections.health-test' => ['driver' => 'unsupported'],
        ]);

        $this->getJson('/up')
            ->assertStatus(503)
            ->assertJsonPath('status', 'degraded')
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('error');
    }
}

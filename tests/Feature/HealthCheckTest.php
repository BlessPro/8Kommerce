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
            ->assertSee('System status')
            ->assertSee('All systems operational')
            ->assertSee('Database')
            ->assertSee('Queue');
    }

    public function test_status_endpoint_can_return_machine_readable_json(): void
    {
        $this->getJson('/up')
            ->assertOk()
            ->assertJsonPath('status', 'operational')
            ->assertJsonCount(4, 'checks')
            ->assertJsonStructure([
                'status',
                'checked_at',
                'response_time_ms',
                'version',
                'checks' => [['name', 'description', 'status', 'response_time_ms']],
            ]);
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

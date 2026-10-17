<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(Request $request): Response|JsonResponse
    {
        $startedAt = hrtime(true);

        $checks = [
            $this->check('Application', 'Web service is responding.', fn () => true),
            $this->check('Database', 'Primary data store is reachable.', fn () => DB::selectOne('select 1')),
            $this->check('Cache', 'Temporary data storage is available.', function (): bool {
                $key = 'health:'.Str::uuid();
                $value = Str::random(24);

                Cache::put($key, $value, 10);
                $available = hash_equals($value, (string) Cache::get($key));
                Cache::forget($key);

                return $available;
            }),
            $this->check('Queue', 'Background work can be accepted.', fn () => $this->checkQueue()),
        ];

        $healthy = collect($checks)->every(fn (array $check): bool => $check['status'] === 'operational');
        $statusCode = $healthy ? 200 : 503;
        $payload = [
            'status' => $healthy ? 'operational' : 'degraded',
            'checked_at' => now()->utc()->toIso8601String(),
            'response_time_ms' => $this->elapsedMilliseconds($startedAt),
            'version' => (string) config('app.version', 'unknown'),
            'checks' => $checks,
        ];

        if ($request->expectsJson() || $request->query('format') === 'json') {
            return response()->json($payload, $statusCode)
                ->header('Cache-Control', 'no-store, max-age=0');
        }

        return response()
            ->view('health', $payload, $statusCode)
            ->header('Cache-Control', 'no-store, max-age=0');
    }

    private function check(string $name, string $description, callable $callback): array
    {
        $startedAt = hrtime(true);

        try {
            $passed = $callback() !== false;
        } catch (Throwable) {
            $passed = false;
        }

        return [
            'name' => $name,
            'description' => $description,
            'status' => $passed ? 'operational' : 'unavailable',
            'response_time_ms' => $this->elapsedMilliseconds($startedAt),
        ];
    }

    private function checkQueue(): bool
    {
        $connectionName = (string) config('queue.default');
        $connection = config("queue.connections.{$connectionName}", []);
        $driver = $connection['driver'] ?? null;

        if ($driver === 'database') {
            $databaseConnection = $connection['connection'] ?? config('database.default');
            DB::connection($databaseConnection)->getPdo();

            return DB::connection($databaseConnection)
                ->getSchemaBuilder()
                ->hasTable((string) ($connection['table'] ?? 'jobs'));
        }

        if ($driver === 'redis') {
            $reply = Redis::connection($connection['connection'] ?? 'default')->command('ping');

            return in_array(strtoupper((string) $reply), ['1', 'PONG'], true);
        }

        return in_array($driver, ['sync', 'deferred', 'background', 'null'], true);
    }

    private function elapsedMilliseconds(int $startedAt): float
    {
        return round((hrtime(true) - $startedAt) / 1_000_000, 1);
    }
}

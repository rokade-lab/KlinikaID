<?php

declare(strict_types=1);

namespace KlinikaID\Http\Controller;

use KlinikaID\Http\AppContext;
use KlinikaID\Http\Request;
use KlinikaID\Http\Response;
use KlinikaID\Infrastructure\Env;
use Throwable;

final class HealthController
{
    public function __construct(private readonly AppContext $app) {}

    public function show(Request $request): Response
    {
        $checks = [
            'db' => $this->checkDatabase() ? 'ok' : 'fail',
            'storage' => $this->checkStorage() ? 'ok' : 'fail',
        ];
        $ok = !in_array('fail', $checks, true);
        return Response::json(['status' => $ok ? 'ok' : 'fail', 'checks' => $checks], $ok ? 200 : 503);
    }

    private function checkDatabase(): bool
    {
        try {
            return (int) $this->app->pdo()->query('SELECT 1')->fetchColumn() === 1;
        } catch (Throwable) {
            return false;
        }
    }

    private function checkStorage(): bool
    {
        try {
            $root = Env::get('STORAGE_ROOT');
            if (!is_dir($root)) {
                return false;
            }
            $probe = rtrim($root, '/') . '/.healthz-' . bin2hex(random_bytes(8));
            $written = @file_put_contents($probe, 'ok', LOCK_EX);
            if ($written !== 2) {
                return false;
            }
            $readBack = @file_get_contents($probe);
            return @unlink($probe) && $readBack === 'ok';
        } catch (Throwable) {
            return false;
        }
    }
}

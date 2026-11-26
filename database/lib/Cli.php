<?php

declare(strict_types=1);

namespace KlinikaID\Data;

use PDOException;
use Throwable;

final class Cli
{
    public static function run(string $command, callable $main): never
    {
        try {
            $result = $main();
            self::emit(['status' => 'ok', 'command' => $command] + $result);
            exit(0);
        } catch (Throwable $e) {
            if ($e instanceof DataException) {
                $out = ['status' => 'error', 'command' => $command, 'error' => $e->errorCode, 'message' => $e->getMessage()];
            } elseif ($e instanceof PDOException) {
                $out = ['status' => 'error', 'command' => $command, 'error' => 'DB_ERROR', 'sqlstate' => (string) $e->getCode()];
            } else {
                $out = ['status' => 'error', 'command' => $command, 'error' => 'INTERNAL_ERROR', 'type' => $e::class];
            }
            self::emit($out);
            fwrite(STDERR, "{$command}: gagal ({$out['error']})\n");
            exit(1);
        }
    }

    private static function emit(array $data): void
    {
        fwrite(STDOUT, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n");
    }
}

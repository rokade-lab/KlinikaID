<?php

declare(strict_types=1);

namespace KlinikaID\Http;

use DateTimeImmutable;
use DateTimeZone;

final class Logger
{
    private const ALLOWED_FIELDS = ['method', 'path', 'status', 'duration_ms', 'user_id', 'role', 'error_class'];

    public function __construct(
        private readonly string $requestId,
        private readonly ?string $file,
    ) {}

    public function info(string $event, array $fields = []): void
    {
        $this->write('info', $event, $fields);
    }

    public function warning(string $event, array $fields = []): void
    {
        $this->write('warning', $event, $fields);
    }

    public function error(string $event, array $fields = []): void
    {
        $this->write('error', $event, $fields);
    }

    private function write(string $level, string $event, array $fields): void
    {
        $record = [
            'ts' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d\TH:i:s.v\Z'),
            'level' => $level,
            'event' => $event,
            'request_id' => $this->requestId,
        ];
        foreach (self::ALLOWED_FIELDS as $name) {
            if (array_key_exists($name, $fields)) {
                $record[$name] = $fields[$name];
            }
        }
        $line = json_encode(
            $record,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR,
        ) . "\n";

        if ($this->file !== null && $this->file !== '') {
            if (@file_put_contents($this->file, $line, FILE_APPEND | LOCK_EX) !== false) {
                return;
            }
        }
        @file_put_contents('php://stderr', $line);
    }

    public static function safePath(string $path): string
    {
        return strlen($path) > 512 ? substr($path, 0, 512) : $path;
    }
}

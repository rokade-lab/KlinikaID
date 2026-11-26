<?php

declare(strict_types=1);

namespace KlinikaID\Infrastructure;

use RuntimeException;

final class Env
{
    public static function get(string $name, ?string $default = null): string
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            if ($default === null) {
                throw new RuntimeException("Konfigurasi wajib tidak tersedia: {$name}");
            }
            return $default;
        }
        return $value;
    }

    public static function secretFile(string $name): string
    {
        $path = self::get($name);
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException("File secret tidak dapat dibaca untuk {$name}");
        }
        $content = file_get_contents($path);
        if ($content === false) {
            throw new RuntimeException("File secret tidak dapat dibaca untuk {$name}");
        }
        return rtrim($content, "\r\n");
    }
}

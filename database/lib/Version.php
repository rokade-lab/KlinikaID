<?php

declare(strict_types=1);

namespace KlinikaID\Data;

final class Version
{
    public static function file(): string
    {
        $env = getenv('KLINIKAID_VERSION_FILE');
        return $env !== false && $env !== '' ? $env : dirname(__DIR__, 2) . '/VERSION';
    }

    public static function seedVersion(): string
    {
        $path = self::file();
        $raw = is_file($path) ? file_get_contents($path) : false;
        if ($raw === false) {
            throw new DataException('VERSION_UNREADABLE', 'File VERSION tidak dapat dibaca');
        }
        foreach (preg_split('/\R/', $raw) ?: [] as $line) {
            if (preg_match('/^\s*seed_version\s*=\s*([A-Za-z0-9._-]+)\s*$/', $line, $m) === 1) {
                return $m[1];
            }
        }
        throw new DataException('SEED_VERSION_MISSING', 'seed_version tidak ada di VERSION');
    }
}

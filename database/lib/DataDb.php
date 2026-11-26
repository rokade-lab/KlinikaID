<?php

declare(strict_types=1);

namespace KlinikaID\Data;

use KlinikaID\Infrastructure\Database;
use PDO;

final class DataDb
{
    public const LOCK_KEY = 4_841_201_001;

    public static function connect(): PDO
    {
        $pdo = Database::connect();
        $pdo->exec("SET TIME ZONE 'UTC'");
        $pdo->exec("SET lock_timeout = '30s'");
        return $pdo;
    }

    public static function withLock(PDO $pdo, callable $fn): mixed
    {
        $pdo->query('SELECT pg_advisory_lock(' . self::LOCK_KEY . ')');
        try {
            return $fn();
        } finally {
            $pdo->query('SELECT pg_advisory_unlock(' . self::LOCK_KEY . ')');
        }
    }
}

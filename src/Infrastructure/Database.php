<?php

declare(strict_types=1);

namespace KlinikaID\Infrastructure;

use PDO;

final class Database
{
    public static function connect(): PDO
    {
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            Env::get('DB_HOST', 'postgres'),
            Env::get('DB_PORT', '5432'),
            Env::get('DB_NAME', 'klinikaid'),
        );

        return new PDO($dsn, Env::get('DB_USER'), Env::secretFile('DB_PASSWORD_FILE'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 3,
        ]);
    }
}

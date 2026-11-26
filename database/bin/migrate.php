<?php

declare(strict_types=1);

use KlinikaID\Data\Cli;
use KlinikaID\Data\DataDb;
use KlinikaID\Data\Migrator;

require __DIR__ . '/bootstrap.php';

Cli::run('migrate', static function (): array {
    $pdo = DataDb::connect();
    return DataDb::withLock($pdo, static fn () => (new Migrator($pdo, dirname(__DIR__) . '/migrations'))->migrate());
});

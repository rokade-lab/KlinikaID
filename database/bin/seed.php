<?php

declare(strict_types=1);

use KlinikaID\Data\Cli;
use KlinikaID\Data\DataDb;
use KlinikaID\Data\SeedDataset;
use KlinikaID\Data\Seeder;
use KlinikaID\Data\Storage;
use KlinikaID\Data\Version;
use KlinikaID\Infrastructure\Env;

require __DIR__ . '/bootstrap.php';

Cli::run('seed', static function (): array {
    $dataset = new SeedDataset(Version::seedVersion());
    $passwords = Seeder::loadLabPasswords(Env::get('LAB_ACCOUNTS_FILE'));
    $storage = Storage::fromEnv(create: true);
    $pdo = DataDb::connect();
    return DataDb::withLock($pdo, static fn () => (new Seeder($pdo, $storage, $passwords))->seed($dataset));
});

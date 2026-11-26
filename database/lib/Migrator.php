<?php

declare(strict_types=1);

namespace KlinikaID\Data;

use PDO;
use Throwable;

final class Migrator
{
    public function __construct(private readonly PDO $pdo, private readonly string $dir)
    {
    }

    public function migrate(): array
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS schema_migrations (
                version text PRIMARY KEY,
                applied_at timestamptz NOT NULL DEFAULT now()
            )'
        );
        $done = array_flip($this->pdo->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN));

        $applied = [];
        foreach ($this->files() as $version => $path) {
            if (isset($done[$version])) {
                continue;
            }
            $sql = file_get_contents($path);
            if ($sql === false) {
                throw new DataException('MIGRATION_UNREADABLE', "Migration tidak dapat dibaca: {$version}");
            }
            $this->pdo->beginTransaction();
            try {
                $this->pdo->exec($sql);
                $stmt = $this->pdo->prepare('INSERT INTO schema_migrations (version) VALUES (?)');
                $stmt->execute([$version]);
                $this->pdo->commit();
            } catch (Throwable $e) {
                $this->pdo->rollBack();
                throw $e;
            }
            $applied[] = $version;
        }

        $versions = array_keys($this->files());
        return ['applied' => $applied, 'current' => $versions === [] ? null : end($versions)];
    }

    private function files(): array
    {
        $out = [];
        foreach (glob($this->dir . '/*.sql') ?: [] as $path) {
            $version = basename($path, '.sql');
            if (preg_match('/^\d{4}_[a-z0-9_]+$/', $version) !== 1) {
                throw new DataException('MIGRATION_NAME_INVALID', "Nama file migration tidak valid: {$version}");
            }
            $out[$version] = $path;
        }
        ksort($out, SORT_STRING);
        return $out;
    }
}

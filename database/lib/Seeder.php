<?php

declare(strict_types=1);

namespace KlinikaID\Data;

use KlinikaID\Storage\DocumentStorage;
use PDO;
use Throwable;

final class Seeder
{
    public const DOMAIN_TABLES = ['patients', 'users', 'visits', 'doctor_assignments', 'documents', 'schedules'];

    public function __construct(
        private readonly PDO $pdo,
        private readonly DocumentStorage $storage,
        private readonly array $labPasswords,
    ) {
    }

    public static function loadLabPasswords(string $path): array
    {
        $raw = is_file($path) && is_readable($path) ? file_get_contents($path) : false;
        $data = $raw === false ? null : json_decode($raw, true);
        if (!is_array($data)) {
            throw new DataException('LAB_ACCOUNTS_INVALID', 'LAB_ACCOUNTS_FILE tidak dapat dibaca sebagai JSON objek');
        }
        $out = [];
        foreach (SeedDataset::LAB_ACCOUNTS as $name) {
            if (!isset($data[$name]) || !is_string($data[$name]) || $data[$name] === '') {
                throw new DataException('LAB_ACCOUNTS_INVALID', "LAB_ACCOUNTS_FILE tidak memuat password untuk {$name}");
            }
            $out[$name] = $data[$name];
        }
        return $out;
    }

    public function seed(SeedDataset $ds): array
    {
        $counts = [];
        foreach (self::DOMAIN_TABLES as $t) {
            $counts[$t] = (int) $this->pdo->query("SELECT count(*) FROM {$t}")->fetchColumn();
        }
        if (array_sum($counts) > 0) {
            $st = $this->pdo->prepare('SELECT count(*) FROM users WHERE username = ANY (?::text[])');
            $st->execute(['{' . implode(',', SeedDataset::LAB_ACCOUNTS) . '}']);
            if ((int) $st->fetchColumn() === count(SeedDataset::LAB_ACCOUNTS)) {
                $stored = $this->pdo->query('SELECT id, storage_key FROM documents ORDER BY id LIMIT 1')->fetch(PDO::FETCH_ASSOC);
                $expectedKey = $stored === false ? null : DocumentStorage::keyFromHash(hash('sha256', 'klinikaid:seed:v' . $ds->seedVersion . ':document:' . $stored['id']));
                if ($stored !== false && $stored['storage_key'] !== $expectedKey) {
                    throw new DataException('SEED_VERSION_MISMATCH', 'Database memakai seed berbeda; cadangkan state lalu reset target untuk mengganti versi seed');
                }
                return ['result' => 'skipped', 'reason' => 'already_seeded', 'seed_version' => $ds->seedVersion, 'counts' => $counts];
            }
            throw new DataException('SEED_STATE_INCONSISTENT', 'Database terisi sebagian; jalankan reset');
        }

        $files = $ds->files();
        foreach ($files as $key => $content) {
            $this->storage->write($key, $content);
        }

        $tables = $ds->tables();
        foreach ($tables['users'] as &$u) {
            $pw = $this->labPasswords[$u['username']] ?? bin2hex(random_bytes(32));
            $u['password_hash'] = password_hash($pw, PASSWORD_DEFAULT);
        }
        unset($u);

        $this->pdo->beginTransaction();
        try {
            foreach (self::DOMAIN_TABLES as $t) {
                $this->insertAll($t, $tables[$t]);
            }
            foreach (self::DOMAIN_TABLES as $t) {
                $this->pdo->query("SELECT setval(pg_get_serial_sequence('{$t}', 'id'), (SELECT max(id) FROM {$t}))");
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return [
            'result' => 'seeded',
            'seed_version' => $ds->seedVersion,
            'counts' => array_map('count', $tables),
            'files_written' => count($files),
        ];
    }

    private function insertAll(string $table, array $rows): void
    {
        if ($rows === []) {
            return;
        }
        $cols = array_keys($rows[0]);
        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $table, implode(', ', $cols), implode(', ', array_fill(0, count($cols), '?')));
        $st = $this->pdo->prepare($sql);
        foreach ($rows as $row) {
            $i = 1;
            foreach ($cols as $c) {
                $v = $row[$c];
                $st->bindValue($i++, $v, match (true) {
                    is_bool($v) => PDO::PARAM_BOOL,
                    is_int($v) => PDO::PARAM_INT,
                    $v === null => PDO::PARAM_NULL,
                    default => PDO::PARAM_STR,
                });
            }
            $st->execute();
        }
    }
}

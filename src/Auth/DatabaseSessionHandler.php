<?php

declare(strict_types=1);

namespace KlinikaID\Auth;

use Closure;
use PDO;
use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface;

final class DatabaseSessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly int $maxLifetime,
        private readonly Closure $userIdProvider,
    ) {}

    public static function key(string $sessionId): string
    {
        return hash('sha256', $sessionId);
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT data FROM sessions WHERE id = :id AND last_activity > now() - make_interval(secs => :ttl)'
        );
        $stmt->execute(['id' => self::key($id), 'ttl' => $this->maxLifetime]);
        $data = $stmt->fetchColumn();
        return is_string($data) ? $data : '';
    }

    public function write(string $id, string $data): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO sessions (id, user_id, data, created_at, last_activity)
             VALUES (:id, :uid, :data, now(), now())
             ON CONFLICT (id) DO UPDATE
               SET user_id = EXCLUDED.user_id, data = EXCLUDED.data, last_activity = now()'
        );
        $stmt->bindValue('id', self::key($id));
        $uid = ($this->userIdProvider)();
        $stmt->bindValue('uid', $uid, $uid === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue('data', $data);
        return $stmt->execute();
    }

    public function destroy(string $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE id = :id');
        return $stmt->execute(['id' => self::key($id)]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE last_activity < now() - make_interval(secs => :ttl)');
        $stmt->execute(['ttl' => max($max_lifetime, $this->maxLifetime)]);
        return $stmt->rowCount();
    }

    public function validateId(string $id): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM sessions WHERE id = :id AND last_activity > now() - make_interval(secs => :ttl)'
        );
        $stmt->execute(['id' => self::key($id), 'ttl' => $this->maxLifetime]);
        return $stmt->fetchColumn() !== false;
    }

    public function updateTimestamp(string $id, string $data): bool
    {
        $stmt = $this->pdo->prepare('UPDATE sessions SET last_activity = now() WHERE id = :id');
        return $stmt->execute(['id' => self::key($id)]);
    }
}

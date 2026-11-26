<?php

declare(strict_types=1);

namespace KlinikaID\Auth;

use PDO;

final class Authenticator
{
    private const DUMMY_HASH = '$2y$12$1PYBONWbsoJtRdLm4IkomOHFA.X8Cq/sD3.MHJ7q4q5mJZaiOjj2K';
    private const MAX_USERNAME = 64;
    private const MAX_PASSWORD = 256;

    public function __construct(private readonly PDO $pdo) {}

    public function attempt(string $username, string $password): ?CurrentUser
    {
        $row = null;
        if ($username !== '' && strlen($username) <= self::MAX_USERNAME && strlen($password) <= self::MAX_PASSWORD) {
            $stmt = $this->pdo->prepare(
                'SELECT id, username, password_hash, role, display_name, patient_id, active FROM users WHERE username = :u'
            );
            $stmt->execute(['u' => $username]);
            $found = $stmt->fetch(PDO::FETCH_ASSOC);
            $row = is_array($found) ? $found : null;
        }

        $hash = $row !== null ? (string) $row['password_hash'] : self::DUMMY_HASH;
        $valid = password_verify($password, $hash);

        if ($row === null || !$valid || !self::isActive($row['active'])) {
            return null;
        }
        return self::toUser($row);
    }

    public function currentUser(Session $session): ?CurrentUser
    {
        $userId = $session->userId();
        if ($userId === null) {
            return null;
        }
        $stmt = $this->pdo->prepare(
            'SELECT id, username, role, display_name, patient_id FROM users WHERE id = :id AND active = true'
        );
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? self::toUser($row) : null;
    }

    private static function isActive(mixed $value): bool
    {
        return $value === true || $value === 't' || $value === 1 || $value === '1';
    }

    private static function toUser(array $row): CurrentUser
    {
        return new CurrentUser(
            (int) $row['id'],
            (string) $row['username'],
            (string) $row['role'],
            $row['patient_id'] === null ? null : (int) $row['patient_id'],
            (string) $row['display_name'],
        );
    }
}

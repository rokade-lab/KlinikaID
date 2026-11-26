<?php
declare(strict_types=1);
namespace KlinikaID\Clinical;
use PDO;
final class ProfileRepository
{
    public function __construct(private readonly PDO $pdo) {}
    public function find(int $id): ?array
    {
        $q = $this->pdo->prepare('SELECT id, username, display_name, role, patient_id, active FROM users WHERE id = :id');
        $q->execute(['id' => $id]);
        return $q->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    public function update(int $id, array $fields): void
    {
        $parts = []; $params = ['id' => $id];
        foreach (['display_name', 'role', 'patient_id'] as $field) {
            if (array_key_exists($field, $fields)) {
                $parts[] = $field . ' = :' . $field;
                $params[$field] = $fields[$field];
            }
        }
        if ($parts === []) return;
        $q = $this->pdo->prepare('UPDATE users SET ' . implode(', ', $parts) . ' WHERE id = :id');
        $q->execute($params);
    }
}

<?php
declare(strict_types=1);
namespace KlinikaID\Clinical;
use KlinikaID\Auth\CurrentUser;
use PDO;
final class DocumentRepository
{
    public function __construct(private readonly PDO $pdo) {}
    public function find(int $id): ?array
    {
        $q = $this->pdo->prepare('SELECT * FROM documents WHERE id = :id');
        $q->execute(['id' => $id]);
        return $q->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    public function findForUpdate(int $id): ?array
    {
        $q = $this->pdo->prepare('SELECT * FROM documents WHERE id = :id FOR UPDATE');
        $q->execute(['id' => $id]);
        return $q->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    public function forVisit(int $visitId): array
    {
        $q = $this->pdo->prepare('SELECT * FROM documents WHERE visit_id = :id ORDER BY id');
        $q->execute(['id' => $visitId]);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }
    public function findByStorageKey(string $key): ?array
    {
        $q = $this->pdo->prepare('SELECT * FROM documents WHERE storage_key = :key');
        $q->execute(['key' => $key]);
        return $q->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    public function delete(int $id): void
    {
        $q = $this->pdo->prepare('DELETE FROM documents WHERE id = :id');
        $q->execute(['id' => $id]);
    }

    public function forUser(CurrentUser $user, int $page = 1): array
    {
        [$where, $params] = VisitRepository::scope($user);
        $q = $this->pdo->prepare('SELECT d.id, d.visit_id, d.display_name, d.size_bytes, v.visit_date, v.department FROM documents d JOIN visits v ON v.id = d.visit_id WHERE ' . $where . ' ORDER BY v.visit_date DESC, d.id DESC LIMIT :limit OFFSET :offset');
        foreach ($params as $key => $value) $q->bindValue($key, $value, PDO::PARAM_INT);
        $q->bindValue('limit', 10, PDO::PARAM_INT);
        $q->bindValue('offset', (max(1, min(100000, $page)) - 1) * 10, PDO::PARAM_INT);
        $q->execute();
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }
    public function countForUser(CurrentUser $user): int
    {
        [$where, $params] = VisitRepository::scope($user);
        $q = $this->pdo->prepare('SELECT count(*) FROM documents d JOIN visits v ON v.id = d.visit_id WHERE ' . $where);
        $q->execute($params);
        return (int) $q->fetchColumn();
    }
}

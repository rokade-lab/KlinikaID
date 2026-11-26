<?php
declare(strict_types=1);
namespace KlinikaID\Clinical;
use KlinikaID\Auth\CurrentUser;
use PDO;
final class VisitRepository
{
    public function __construct(private readonly PDO $pdo) {}
    public function find(int $id): ?array
    {
        $q = $this->pdo->prepare('SELECT v.*, p.full_name, p.medical_record_no FROM visits v JOIN patients p ON p.id = v.patient_id WHERE v.id = :id');
        $q->execute(['id' => $id]);
        return $q->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function forUser(CurrentUser $user, int $page = 1): array
    {
        [$where, $params] = self::scope($user);
        $q = $this->pdo->prepare('SELECT v.id, v.visit_date, v.department, s.room, s.status, (SELECT count(*) FROM documents d WHERE d.visit_id = v.id) AS document_count FROM visits v LEFT JOIN schedules s ON s.visit_id = v.id WHERE ' . $where . ' ORDER BY v.visit_date DESC, v.id DESC LIMIT :limit OFFSET :offset');
        foreach ($params as $key => $value) $q->bindValue($key, $value, PDO::PARAM_INT);
        $q->bindValue('limit', 10, PDO::PARAM_INT);
        $q->bindValue('offset', (max(1, min(100000, $page)) - 1) * 10, PDO::PARAM_INT);
        $q->execute();
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }
    public function countForUser(CurrentUser $user): int
    {
        [$where, $params] = self::scope($user);
        $q = $this->pdo->prepare('SELECT count(*) FROM visits v WHERE ' . $where);
        $q->execute($params);
        return (int) $q->fetchColumn();
    }

    public static function scope(CurrentUser $user): array
    {
        return match ($user->role) {
            'patient' => $user->patientId === null ? ['FALSE', []] : ['v.patient_id = :scope', ['scope' => $user->patientId]],
            'doctor' => ['EXISTS (SELECT 1 FROM doctor_assignments a WHERE a.visit_id = v.id AND a.doctor_id = :scope AND a.active)', ['scope' => $user->id]],
            default => ['FALSE', []],
        };
    }
}

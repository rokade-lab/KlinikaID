<?php
declare(strict_types=1);
namespace KlinikaID\Clinical;
use PDO;
final class AssignmentRepository
{
    public function __construct(private readonly PDO $pdo) {}
    public function isActive(int $doctorId, int $visitId): bool
    {
        $q = $this->pdo->prepare('SELECT 1 FROM doctor_assignments WHERE doctor_id = :doctor AND visit_id = :visit AND active');
        $q->execute(['doctor' => $doctorId, 'visit' => $visitId]);
        return $q->fetchColumn() !== false;
    }
    public function all(): array
    {
        return $this->pdo->query('SELECT a.id, a.doctor_id, a.visit_id, a.active, u.display_name FROM doctor_assignments a JOIN users u ON u.id = a.doctor_id ORDER BY a.id')->fetchAll(PDO::FETCH_ASSOC);
    }
    public function doctors(): array
    {
        $q = $this->pdo->prepare('SELECT id, username, display_name, active FROM users WHERE role = :role ORDER BY id');
        $q->execute(['role' => 'doctor']);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }
    public function visits(): array
    {
        return $this->pdo->query('SELECT id, visit_date, department, patient_id FROM visits ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    }
    public function assign(int $doctorId, int $visitId): void
    {
        $q = $this->pdo->prepare('INSERT INTO doctor_assignments (doctor_id, visit_id, active, assigned_at) VALUES (:doctor, :visit, true, now()) ON CONFLICT (doctor_id, visit_id) WHERE active DO NOTHING');
        $q->execute(['doctor' => $doctorId, 'visit' => $visitId]);
    }
    public function revoke(int $doctorId, int $visitId): void
    {
        $q = $this->pdo->prepare('UPDATE doctor_assignments SET active = false, revoked_at = now() WHERE doctor_id = :doctor AND visit_id = :visit AND active');
        $q->execute(['doctor' => $doctorId, 'visit' => $visitId]);
    }
}

<?php
declare(strict_types=1);
namespace KlinikaID\Clinical;
use KlinikaID\Auth\CurrentUser;
use PDO;
final class ScheduleRepository
{
    public function __construct(private readonly PDO $pdo) {}
    public function forUser(CurrentUser $user, int $page = 1, string $search = ''): array
    {
        $where = match ($user->role) {
            'patient' => 'v.patient_id = :scope',
            'doctor' => 'EXISTS (SELECT 1 FROM doctor_assignments a WHERE a.visit_id = v.id AND a.doctor_id = :scope AND a.active)',
            'frontdesk', 'admin' => 'TRUE',
            default => 'FALSE',
        };
        $sql = 'SELECT s.id, s.visit_id, s.operational_time, s.room, s.status, v.visit_date, v.department FROM schedules s JOIN visits v ON v.id = s.visit_id WHERE ' . $where . ' AND (s.room ILIKE :search OR v.department ILIKE :department) ORDER BY s.operational_time, s.id LIMIT :limit OFFSET :offset';
        $q = $this->pdo->prepare($sql);
        if ($user->role === 'patient' || $user->role === 'doctor') $q->bindValue('scope', $user->role === 'patient' ? $user->patientId : $user->id, PDO::PARAM_INT);
        $q->bindValue('search', '%' . $search . '%');
        $q->bindValue('department', '%' . $search . '%');
        $q->bindValue('limit', 10, PDO::PARAM_INT);
        $q->bindValue('offset', (max(1, min(100000, $page)) - 1) * 10, PDO::PARAM_INT);
        $q->execute();
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }
    public function countForUser(CurrentUser $user, string $search = ''): int
    {
        $where = match ($user->role) {
            'patient' => 'v.patient_id = :scope',
            'doctor' => 'EXISTS (SELECT 1 FROM doctor_assignments a WHERE a.visit_id = v.id AND a.doctor_id = :scope AND a.active)',
            'frontdesk', 'admin' => 'TRUE', default => 'FALSE',
        };
        $q = $this->pdo->prepare('SELECT count(*) FROM schedules s JOIN visits v ON v.id = s.visit_id WHERE ' . $where . ' AND (s.room ILIKE :search OR v.department ILIKE :department)');
        $params = in_array($user->role, ['patient', 'doctor'], true) ? ['scope' => $user->role === 'patient' ? $user->patientId : $user->id] : [];
        $params['search'] = '%' . $search . '%';
        $params['department'] = '%' . $search . '%';
        $q->execute($params);
        return (int) $q->fetchColumn();
    }
}

<?php

declare(strict_types=1);
namespace KlinikaID\Audit;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use KlinikaID\Http\AppContext;

final class Audit
{
    public function __construct(private readonly AppContext $app) {}

    public function record(string $action, string $resourceType, int|string|null $resourceId, string $outcome, array $context = []): void
    {
        if (!in_array($outcome, ['allow', 'deny', 'error'], true)) {
            throw new InvalidArgumentException('Hasil audit tidak valid');
        }
        $actor = $this->app->currentUser();
        $id = $resourceId === null ? null : (string) $resourceId;
        $statement = $this->app->pdo()->prepare(
            'INSERT INTO audit_events (actor_id, action, resource_type, resource_id, outcome, request_id)
             VALUES (:actor, :action, :type, :id, :outcome, :request)'
        );
        $statement->execute([
            'actor' => $actor?->id, 'action' => $action, 'type' => $resourceType,
            'id' => $id, 'outcome' => $outcome, 'request' => $this->app->request->requestId,
        ]);

        $details = [];
        foreach (['full_name', 'medical_note_dummy'] as $key) {
            if (isset($context[$key]) && is_string($context[$key])) {
                $details[$key] = $context[$key];
            }
        }
        $entry = [
            'ts' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d\TH:i:s.v\Z'),
            'level' => 'info', 'event' => 'clinical.audit',
            'request_id' => $this->app->request->requestId,
            'actor_id' => $actor?->id, 'action' => $action,
            'resource_type' => $resourceType, 'resource_id' => $id,
            'outcome' => $outcome, 'context' => $details,
        ];
        $line = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR) . "\n";
        $sink = getenv('LOG_FILE') ?: 'php://stderr';
        if (@file_put_contents($sink, $line, FILE_APPEND | LOCK_EX) === false) {
            @file_put_contents('php://stderr', $line);
        }
    }
}

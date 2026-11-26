<?php

declare(strict_types=1);

namespace KlinikaID\Clinical;

use InvalidArgumentException;
use KlinikaID\Audit\Audit;
use KlinikaID\Http\AppContext;
use KlinikaID\Http\ErrorResponder;
use KlinikaID\Http\Request;
use KlinikaID\Http\Response;
use KlinikaID\Storage\DocumentStorage;
use RuntimeException;

final class DocumentViewResponder
{
    public function __construct(private readonly AppContext $app) {}

    public function respond(Request $request, ?array $document, bool $attachment = false): Response
    {
        $visit = $document === null ? null : (new VisitRepository($this->app->pdo()))->find((int) $document['visit_id']);
        if ($document === null || $visit === null) {
            return $this->notFound($request, $document['id'] ?? null);
        }
        try {
            $content = (new DocumentStorage(getenv('STORAGE_ROOT') ?: ''))->read($document['storage_key']);
        } catch (InvalidArgumentException | RuntimeException) {
            return $this->notFound($request, $document['id']);
        }
        (new Audit($this->app))->record($attachment ? 'document.download' : 'document.view', 'document', $document['id'], 'allow', [
            'full_name' => $visit['full_name'],
            'medical_note_dummy' => $visit['medical_note_dummy'],
        ]);

        $disposition = $attachment ? 'attachment' : 'inline';
        return new Response(200, $content, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="dokumen-' . (int) $document['id'] . '.pdf"',
            'Content-Length' => (string) strlen($content),
            'Cache-Control' => 'no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function notFound(Request $request, int|string|null $id): Response
    {
        (new Audit($this->app))->record('document.read', 'document', $id, 'deny');
        return (new ErrorResponder($this->app->view()))->respond($request, 404, $this->app->currentUser());
    }
}

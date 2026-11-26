<?php

declare(strict_types=1);

namespace KlinikaID\Clinical;

use KlinikaID\Audit\Audit;
use KlinikaID\Http\AppContext;
use KlinikaID\Http\ErrorResponder;
use KlinikaID\Http\Request;
use KlinikaID\Http\Response;

final class ExportController
{
    public function __construct(private readonly AppContext $app) {}

    public function export(Request $request): Response
    {
        $user = $this->app->currentUser();

        if ($user === null || !$user->hasRole('patient', 'doctor')) {
            (new Audit($this->app))->record('visit.export', 'visit', null, 'deny');
            return (new ErrorResponder($this->app->view()))->respond($request, 403, $user);
        }
        $id = filter_var($request->query['visit_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $visit = $id === false ? null : (new VisitRepository($this->app->pdo()))->find($id);
        $format = $request->query['format'] ?? 'csv';
        if ($visit === null || !in_array($format, ['json', 'csv'], true)) {
            (new Audit($this->app))->record('visit.export', 'visit', $id === false ? null : $id, 'deny');
            return (new ErrorResponder($this->app->view()))->respond($request, 404, $user);
        }
        $data = [
            'id' => (int) $visit['id'],
            'patient_id' => (int) $visit['patient_id'],
            'full_name' => $visit['full_name'],
            'visit_date' => $visit['visit_date'],
            'department' => $visit['department'],
            'medical_note_dummy' => $visit['medical_note_dummy'],
        ];
        (new Audit($this->app))->record('visit.export', 'visit', $visit['id'], 'allow', [
            'full_name' => $visit['full_name'],
            'medical_note_dummy' => $visit['medical_note_dummy'],
        ]);
        if ($format === 'json') {
            return Response::json(['visit' => $data])->withHeader('Content-Disposition', 'attachment; filename="kunjungan-' . $id . '.json"');
        }
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new \RuntimeException('Export tidak dapat dibuat');
        }
        fputcsv($stream, array_keys($data), escape: '');
        $cells = array_map(static function (int|string $value): string {
            $text = (string) $value;

            return preg_match('/^[\\x00-\\x20]*[=+@-]|^[\\t\\r\\n]/', $text) === 1 ? "'" . $text : $text;
        }, array_values($data));
        fputcsv($stream, $cells, escape: '');
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);
        if ($csv === false) {
            throw new \RuntimeException('Export tidak dapat dibuat');
        }
        return new Response(200, $csv, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="kunjungan-' . $id . '.csv"',
            'Cache-Control' => 'no-store',
        ]);
    }
}

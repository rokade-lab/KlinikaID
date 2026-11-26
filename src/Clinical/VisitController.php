<?php

declare(strict_types=1);

namespace KlinikaID\Clinical;

use KlinikaID\Audit\Audit;
use KlinikaID\Http\AppContext;
use KlinikaID\Http\ErrorResponder;
use KlinikaID\Http\Request;
use KlinikaID\Http\Response;

final class VisitController
{
    public function __construct(private readonly AppContext $app) {}

    public function show(Request $request): Response
    {
        $id = filter_var($request->query['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $visit = $id === false ? null : (new VisitRepository($this->app->pdo()))->find($id);
        if ($visit === null) {
            (new Audit($this->app))->record('visit.view', 'visit', $id === false ? null : $id, 'deny');
            return (new ErrorResponder($this->app->view()))->respond($request, 404, $this->app->currentUser());
        }

        $documents = (new DocumentRepository($this->app->pdo()))->forVisit((int) $visit['id']);
        (new Audit($this->app))->record('visit.view', 'visit', $visit['id'], 'allow', [
            'full_name' => $visit['full_name'],
            'medical_note_dummy' => $visit['medical_note_dummy'],
        ]);
        return Response::html($this->app->view()->page('kunjungan/detail', 'Detail kunjungan', [
            'visit' => $visit,
            'documents' => $documents,
            'activePath' => '/kunjungan',
        ], $this->app->currentUser()));
    }
}

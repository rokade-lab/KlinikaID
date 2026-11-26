<?php

declare(strict_types=1);
namespace KlinikaID\Clinical;

use KlinikaID\Audit\Audit;
use KlinikaID\Http\AppContext;
use KlinikaID\Http\Request;
use KlinikaID\Http\Response;

final class VisitListController
{
    public function __construct(private readonly AppContext $app) {}

    public function visits(Request $request): Response
    {
        $user = $this->app->currentUser() ?? throw new \LogicException('Route user tanpa user');
        $page = max(1, min(100000, (int) ($request->query['page'] ?? 1)));
        $repository = new VisitRepository($this->app->pdo());
        (new Audit($this->app))->record('visit.list', 'visit', null, 'allow');
        return Response::html($this->app->view()->page('kunjungan/list', 'Riwayat kunjungan', [
            'rows' => $repository->forUser($user, $page),
            'total' => $repository->countForUser($user),
            'page' => $page, 'activePath' => '/kunjungan/riwayat',
        ], $user));
    }

    public function documents(Request $request): Response
    {
        $user = $this->app->currentUser() ?? throw new \LogicException('Route user tanpa user');
        $page = max(1, min(100000, (int) ($request->query['page'] ?? 1)));
        $repository = new DocumentRepository($this->app->pdo());
        (new Audit($this->app))->record('document.list', 'document', null, 'allow');
        return Response::html($this->app->view()->page('dokumen/index', 'Dokumen pemeriksaan', [
            'rows' => $repository->forUser($user, $page),
            'total' => $repository->countForUser($user),
            'page' => $page, 'activePath' => '/dokumen/daftar',
        ], $user));
    }
}

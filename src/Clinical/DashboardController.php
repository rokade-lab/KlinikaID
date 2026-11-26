<?php

declare(strict_types=1);
namespace KlinikaID\Clinical;

use KlinikaID\Audit\Audit;
use KlinikaID\Http\AppContext;
use KlinikaID\Http\Request;
use KlinikaID\Http\Response;
use KlinikaID\View\View;

final class DashboardController
{
    public function __construct(private readonly AppContext $app) {}

    public function index(Request $request): Response
    {
        $user = $this->app->currentUser() ?? throw new \LogicException('Route user tanpa user');
        $page = max(1, min(100000, (int) ($request->query['page'] ?? 1)));
        $search = substr($request->query['q'] ?? '', 0, 100);
        $repository = new ScheduleRepository($this->app->pdo());
        (new Audit($this->app))->record('dashboard.view', 'dashboard', null, 'allow');
        return Response::html($this->app->view()->page('dasbor/index', 'Dasbor', [
            'roleLabel' => View::roleLabel($user->role),
            'rows' => $repository->forUser($user, $page, $search),
            'total' => $repository->countForUser($user, $search),
            'clinicalLinks' => $user->hasRole('patient', 'doctor'),
            'page' => $page, 'search' => $search, 'activePath' => '/dasbor',
        ], $user));
    }
}

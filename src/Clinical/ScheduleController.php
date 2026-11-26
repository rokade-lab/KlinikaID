<?php

declare(strict_types=1);
namespace KlinikaID\Clinical;

use KlinikaID\Audit\Audit;
use KlinikaID\Http\AppContext;
use KlinikaID\Http\Request;
use KlinikaID\Http\Response;

final class ScheduleController
{
    public function __construct(private readonly AppContext $app) {}

    public function index(Request $request): Response
    {
        $user = $this->app->currentUser() ?? throw new \LogicException('Route user tanpa user');
        $page = max(1, min(100000, (int) ($request->query['page'] ?? 1)));
        $search = substr($request->query['q'] ?? '', 0, 100);
        $repository = new ScheduleRepository($this->app->pdo());
        (new Audit($this->app))->record('schedule.view', 'schedule', null, 'allow');
        return Response::html($this->app->view()->page('jadwal/index', 'Jadwal Klinik', [
            'rows' => $repository->forUser($user, $page, $search),
            'total' => $repository->countForUser($user, $search),
            'clinicalLinks' => $user->hasRole('patient', 'doctor'),
            'page' => $page, 'search' => $search, 'activePath' => '/jadwal',
        ], $user));
    }
}

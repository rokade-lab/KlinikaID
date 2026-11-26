<?php

declare(strict_types=1);

namespace KlinikaID\Http\Controller;

use KlinikaID\Http\AppContext;
use KlinikaID\Http\Request;
use KlinikaID\Http\Response;
use KlinikaID\View\Navigation;
use KlinikaID\View\View;
use LogicException;

final class DashboardController
{
    public function __construct(private readonly AppContext $app) {}

    public function index(Request $request): Response
    {
        $user = $this->app->currentUser() ?? throw new LogicException('Route user tanpa user');
        $html = $this->app->view()->page('dashboard', 'Dasbor', [
            'roleLabel' => View::roleLabel($user->role),
            'upcoming' => array_values(array_filter(
                Navigation::forRole($user->role),
                static fn (array $item): bool => $item['href'] === null,
            )),
            'activePath' => '/dasbor',
        ], $user);
        return Response::html($html);
    }
}

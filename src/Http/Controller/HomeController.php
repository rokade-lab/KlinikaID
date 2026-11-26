<?php

declare(strict_types=1);

namespace KlinikaID\Http\Controller;

use KlinikaID\Http\AppContext;
use KlinikaID\Http\Request;
use KlinikaID\Http\Response;

final class HomeController
{
    public function __construct(private readonly AppContext $app) {}

    public function index(Request $request): Response
    {
        return Response::redirect($this->app->currentUser() === null ? '/login' : '/dasbor');
    }
}

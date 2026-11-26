<?php

declare(strict_types=1);

namespace KlinikaID\Http\Controller;

use KlinikaID\Http\AppContext;
use KlinikaID\Http\Request;
use KlinikaID\Http\Response;
use LogicException;

final class MeController
{
    public function __construct(private readonly AppContext $app) {}

    public function show(Request $request): Response
    {
        $user = $this->app->currentUser() ?? throw new LogicException('Route user tanpa user');
        return Response::json([
            'id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
            'display_name' => $user->displayName,
            'patient_id' => $user->patientId,
        ]);
    }
}

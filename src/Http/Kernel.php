<?php

declare(strict_types=1);

namespace KlinikaID\Http;

use Throwable;

final class Kernel
{
    public function __construct(
        private readonly Router $router,
        private readonly AppContext $app,
    ) {}

    public function handle(Request $request): Response
    {
        $start = hrtime(true);
        $errors = new ErrorResponder($this->app->view());
        $logger = $this->app->logger();

        try {
            $response = $this->dispatch($request, $errors);
            $this->app->closeSession();
        } catch (Throwable $e) {
            $logger->error('app.error', [
                'method' => $request->method,
                'path' => Logger::safePath($request->path),
                'error_class' => $e::class,
                'user_id' => $this->app->knownUser()?->id,
                'role' => $this->app->knownUser()?->role,
            ]);
            $this->app->abortSession();
            $response = $errors->respond($request, 500);
        }

        $response = $response->withHeader('X-Request-Id', $request->requestId);
        $user = $this->app->knownUser();
        $logger->info('http.request', [
            'method' => $request->method,
            'path' => Logger::safePath($request->path),
            'status' => $response->status,
            'duration_ms' => round((hrtime(true) - $start) / 1e6, 2),
            'user_id' => $user?->id,
            'role' => $user?->role,
        ]);
        return $response;
    }

    private function dispatch(Request $request, ErrorResponder $errors): Response
    {
        $match = $this->router->match($request);
        if ($match->route === null) {
            $response = $errors->respond($request, $match->status);
            if ($match->status === 405) {
                $response = $response->withHeader('Allow', implode(', ', $match->allowed));
            }
            return $response;
        }
        $route = $match->route;

        if ($route->auth === 'user' && $this->app->currentUser() === null) {
            return $request->isApi()
                ? $errors->respond($request, 401)
                : Response::redirect('/login');
        }
        if ($route->auth === 'guest') {
            if ($this->app->currentUser() !== null) {
                return Response::redirect('/dasbor');
            }
        }

        $controller = new ($route->controller)($this->app);
        $response = $controller->{$route->action}($request);
        if (!$response instanceof Response) {
            throw new \LogicException('Controller harus mengembalikan Response');
        }
        return $response;
    }
}

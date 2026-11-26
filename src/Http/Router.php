<?php

declare(strict_types=1);

namespace KlinikaID\Http;

use InvalidArgumentException;

final class Router
{
    private const AUTH_MODES = ['public', 'guest', 'user'];

    private array $routes = [];

    public function __construct(array $table)
    {
        foreach ($table as $entry) {
            [$methods, $path, $handler] = $entry;
            $options = $entry[3] ?? [];
            $auth = $options['auth'] ?? 'user';
            if (!in_array($auth, self::AUTH_MODES, true)) {
                throw new InvalidArgumentException("Opsi auth tidak dikenal untuk {$path}");
            }
            foreach ((array) $methods as $method) {
                $method = strtoupper($method);
                $this->routes[$path][$method] = new Route($method, $path, $handler[0], $handler[1], $auth);
            }
        }
    }

    public static function fromFile(string $file): self
    {
        $table = require $file;
        return new self($table);
    }

    public function match(Request $request): RouteMatch
    {
        $byMethod = $this->routes[$request->path] ?? null;
        if ($byMethod === null) {
            return RouteMatch::notFound();
        }
        $route = $byMethod[$request->routingMethod()] ?? null;
        if ($route === null) {
            return RouteMatch::methodNotAllowed($this->allowedMethods($byMethod));
        }
        return RouteMatch::found($route);
    }

    private function allowedMethods(array $byMethod): array
    {
        $methods = array_keys($byMethod);
        if (in_array('GET', $methods, true)) {
            $methods[] = 'HEAD';
        }
        return array_values(array_unique($methods));
    }
}

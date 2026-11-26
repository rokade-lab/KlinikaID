<?php

declare(strict_types=1);

namespace KlinikaID\Http;

final class RouteMatch
{
    private function __construct(
        public readonly int $status,
        public readonly ?Route $route,
        public readonly array $allowed,
    ) {}

    public static function found(Route $route): self
    {
        return new self(200, $route, []);
    }

    public static function notFound(): self
    {
        return new self(404, null, []);
    }

    public static function methodNotAllowed(array $allowed): self
    {
        return new self(405, null, $allowed);
    }
}

<?php

declare(strict_types=1);

namespace KlinikaID\Http;

final class Request
{
    private const REQUEST_ID_PATTERN = '/^[A-Za-z0-9-]{8,64}$/';

    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $post,
        public readonly array $cookies,
        public readonly array $headers,
        public readonly string $requestId,
    ) {}

    public static function fromGlobals(): self
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH);
        if (!is_string($path) || $path === '' || $path[0] !== '/') {
            $path = '/';
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (is_string($key) && str_starts_with($key, 'HTTP_') && is_string($value)) {
                $headers[strtolower(str_replace('_', '-', substr($key, 5)))] = $value;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE']) && is_string($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = $_SERVER['CONTENT_TYPE'];
        }

        return new self(
            $method,
            $path,
            self::stringMap($_GET),
            $_POST,
            self::stringMap($_COOKIE),
            $headers,
            self::resolveRequestId($_SERVER['HTTP_X_REQUEST_ID'] ?? null),
        );
    }

    public static function resolveRequestId(mixed $candidate): string
    {
        if (is_string($candidate) && preg_match(self::REQUEST_ID_PATTERN, $candidate) === 1) {
            return $candidate;
        }
        return bin2hex(random_bytes(16));
    }

    public function isApi(): bool
    {
        return $this->path === '/api' || str_starts_with($this->path, '/api/');
    }

    public function routingMethod(): string
    {
        return $this->method === 'HEAD' ? 'GET' : $this->method;
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    public function cookie(string $name): ?string
    {
        return $this->cookies[$name] ?? null;
    }

    public function postString(string $name): string
    {
        $value = $this->post[$name] ?? '';
        return is_string($value) ? $value : '';
    }

    private static function stringMap(array $source): array
    {
        $out = [];
        foreach ($source as $key => $value) {
            if (is_string($value)) {
                $out[(string) $key] = $value;
            }
        }
        return $out;
    }
}

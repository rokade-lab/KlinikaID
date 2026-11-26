<?php

declare(strict_types=1);

namespace KlinikaID\Http;

final class Response
{
    private array $headers = [];

    public function __construct(
        public readonly int $status = 200,
        public readonly string $body = '',
        array $headers = [],
    ) {
        foreach ($headers as $name => $value) {
            $this->headers[$name] = $value;
        }
    }

    public static function html(string $body, int $status = 200): self
    {
        return new self($status, $body, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }

    public static function json(array $data, int $status = 200): self
    {
        $body = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        return new self($status, $body, [
            'Content-Type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }

    public static function redirect(string $path, int $status = 302): self
    {
        if ($path === '' || $path[0] !== '/' || str_starts_with($path, '//')) {
            $path = '/';
        }
        return new self($status, '', ['Location' => $path, 'Cache-Control' => 'no-store']);
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;
        return $clone;
    }

    public function header(string $name): ?string
    {
        foreach ($this->headers as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return $value;
            }
        }
        return null;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function send(bool $withBody = true): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);
            foreach ($this->headers as $name => $value) {
                header($name . ': ' . str_replace(["\r", "\n"], '', $value), true);
            }
        }
        if ($withBody) {
            echo $this->body;
        }
    }
}

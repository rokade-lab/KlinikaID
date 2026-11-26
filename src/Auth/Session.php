<?php

declare(strict_types=1);

namespace KlinikaID\Auth;

use PDO;
use RuntimeException;

final class Session
{
    public const COOKIE_NAME = 'klinikaid_sid';
    public const DEFAULT_IDLE_SECONDS = 1800;
    private const USER_KEY = '_uid';

    private bool $started = false;

    public function __construct(
        private readonly PDO $pdo,
        private readonly int $idleSeconds = self::DEFAULT_IDLE_SECONDS,
    ) {}

    public function start(): void
    {
        if ($this->started) {
            return;
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Session sudah aktif di luar kelas Session');
        }
        $handler = new DatabaseSessionHandler(
            $this->pdo,
            $this->idleSeconds,
            fn (): ?int => $this->userId(),
        );
        session_set_save_handler($handler, false);
        $ok = session_start([
            'name' => self::COOKIE_NAME,
            'use_strict_mode' => 1,
            'use_cookies' => 1,
            'use_only_cookies' => 1,
            'use_trans_sid' => 0,
            'cookie_secure' => 0,
            'cookie_httponly' => 1,
            'cookie_samesite' => '',
            'cookie_path' => '/',
            'cookie_domain' => '',
            'cookie_lifetime' => 0,
            'gc_maxlifetime' => $this->idleSeconds,
            'gc_probability' => 1,
            'gc_divisor' => 100,
            'lazy_write' => 1,
            'cache_limiter' => '',
        ]);
        if (!$ok) {
            throw new RuntimeException('Session tidak dapat dimulai');
        }
        $this->started = true;
    }

    public function isStarted(): bool
    {
        return $this->started;
    }

    public function userId(): ?int
    {
        $value = $this->started ? ($_SESSION[self::USER_KEY] ?? null) : null;
        return is_int($value) ? $value : null;
    }

    public function login(int $userId): void
    {
        $this->start();
        $_SESSION = [self::USER_KEY => $userId];
    }

    public function logout(): void
    {
        if (!$this->started) {
            return;
        }
        $_SESSION = [];
        session_destroy();
        $this->started = false;
        setcookie(self::COOKIE_NAME, '', [
            'expires' => 1,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->start();
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->start();
        if ($key === self::USER_KEY) {
            throw new RuntimeException('Kunci session dicadangkan');
        }
        $_SESSION[$key] = $value;
    }

    public function close(): void
    {
        if ($this->started) {
            if ($_SESSION === []) {
                session_abort();
                $this->started = false;
                return;
            }
            session_write_close();
            $this->started = false;
        }
    }

    public function abort(): void
    {
        if ($this->started) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_abort();
            }
            $this->started = false;
        }
    }
}

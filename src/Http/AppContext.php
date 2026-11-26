<?php

declare(strict_types=1);

namespace KlinikaID\Http;

use KlinikaID\Auth\Authenticator;
use KlinikaID\Auth\CurrentUser;
use KlinikaID\Auth\Session;
use KlinikaID\Infrastructure\Database;
use KlinikaID\View\View;
use PDO;

final class AppContext
{
    private ?PDO $pdo = null;
    private ?View $view = null;
    private ?Session $session = null;
    private ?Authenticator $authenticator = null;
    private ?Logger $logger = null;
    private ?CurrentUser $user = null;
    private bool $userResolved = false;

    public function __construct(
        public readonly string $rootDir,
        public readonly Request $request,
    ) {}

    public function pdo(): PDO
    {
        return $this->pdo ??= Database::connect();
    }

    public function view(): View
    {
        return $this->view ??= new View($this->rootDir . '/templates');
    }

    public function session(): Session
    {
        return $this->session ??= new Session($this->pdo());
    }

    public function authenticator(): Authenticator
    {
        return $this->authenticator ??= new Authenticator($this->pdo());
    }

    public function currentUser(): ?CurrentUser
    {
        if (!$this->userResolved) {
            $this->userResolved = true;
            if ($this->request->cookie(Session::COOKIE_NAME) !== null) {
                $session = $this->session();
                $session->start();
                $this->user = $this->authenticator()->currentUser($session);
            }
        }
        return $this->user;
    }

    public function setCurrentUser(?CurrentUser $user): void
    {
        $this->user = $user;
        $this->userResolved = true;
    }

    public function knownUser(): ?CurrentUser
    {
        return $this->userResolved ? $this->user : null;
    }

    public function logger(): Logger
    {
        return $this->logger ??= new Logger($this->request->requestId, getenv('LOG_FILE') ?: null);
    }

    public function closeSession(): void
    {
        $this->session?->close();
    }

    public function abortSession(): void
    {
        $this->session?->abort();
    }
}

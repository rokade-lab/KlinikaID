<?php

declare(strict_types=1);

namespace KlinikaID\Auth;

final class CurrentUser
{
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly string $role,
        public readonly ?int $patientId,
        public readonly string $displayName,
    ) {}

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }
}

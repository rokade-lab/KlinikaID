<?php

declare(strict_types=1);

namespace KlinikaID\View;

use KlinikaID\Auth\CurrentUser;
use RuntimeException;

final class View
{
    public function __construct(private readonly string $templateDir) {}

    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }

    public function page(string $template, string $title, array $vars = [], ?CurrentUser $user = null): string
    {
        $content = $this->render($template, $vars + ['user' => $user]);
        return $this->render('layout', [
            'title' => $title,
            'content' => $content,
            'user' => $user,
            'navigation' => $user === null ? [] : Navigation::forRole($user->role),
            'activePath' => $vars['activePath'] ?? null,
        ]);
    }

    public function render(string $template, array $vars = []): string
    {
        if (preg_match('/^[a-z0-9_\/-]+$/', $template) !== 1) {
            throw new RuntimeException('Nama template tidak valid');
        }
        $file = $this->templateDir . '/' . $template . '.php';
        if (!is_file($file)) {
            throw new RuntimeException('Template tidak ditemukan');
        }
        $render = function (string $__file, array $__vars): string {
            extract($__vars, EXTR_SKIP);
            ob_start();
            try {
                require $__file;
                return (string) ob_get_contents();
            } finally {
                ob_end_clean();
            }
        };
        return $render($file, $vars);
    }

    public static function roleLabel(string $role): string
    {
        return match ($role) {
            'patient' => 'Pasien',
            'doctor' => 'Dokter',
            'frontdesk' => 'Frontdesk',
            'admin' => 'Admin',
            default => 'Tidak dikenal',
        };
    }
}

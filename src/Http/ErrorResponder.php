<?php

declare(strict_types=1);

namespace KlinikaID\Http;

use KlinikaID\Auth\CurrentUser;
use KlinikaID\View\View;
use Throwable;

final class ErrorResponder
{
    private const ERRORS = [
        401 => ['unauthenticated', 'Perlu masuk', 'Silakan masuk terlebih dahulu untuk mengakses halaman ini.'],
        403 => ['forbidden', 'Akses ditolak', 'Anda tidak memiliki izin untuk melakukan tindakan ini.'],
        404 => ['not_found', 'Halaman tidak ditemukan', 'Halaman yang Anda cari tidak tersedia.'],
        405 => ['method_not_allowed', 'Metode tidak didukung', 'Metode permintaan ini tidak didukung untuk alamat tersebut.'],
        500 => ['internal_error', 'Terjadi kesalahan', 'Terjadi kesalahan pada sistem. Silakan coba lagi beberapa saat lagi.'],
    ];

    public function __construct(private readonly View $view) {}

    public function respond(Request $request, int $status, ?CurrentUser $user = null): Response
    {
        [$code, $title, $message] = self::ERRORS[$status] ?? self::ERRORS[500];
        if ($request->isApi()) {
            return Response::json(['error' => $code], $status);
        }
        try {
            $html = $this->view->page('error', $title, [
                'status' => $status,
                'heading' => $title,
                'message' => $message,
                'requestId' => $request->requestId,
            ], $user);
        } catch (Throwable) {
            $html = '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>KlinikaID</title></head>'
                . '<body><h1>' . View::e($title) . '</h1><p>' . View::e($message) . '</p></body></html>';
        }
        return Response::html($html, $status);
    }
}

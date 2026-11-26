<?php

declare(strict_types=1);

use KlinikaID\Http\Controller\AuthController;
use KlinikaID\Clinical\DashboardController;
use KlinikaID\Http\Controller\HealthController;
use KlinikaID\Http\Controller\HomeController;
use KlinikaID\Http\Controller\MeController;

return [
    ['GET',  '/',        [HomeController::class, 'index'],      ['auth' => 'public']],
    ['GET',  '/login',   [AuthController::class, 'showLogin'],  ['auth' => 'guest']],
    ['POST', '/login',   [AuthController::class, 'login'],      ['auth' => 'guest']],
    ['POST', '/logout',  [AuthController::class, 'logout'],     ['auth' => 'user']],
    ['GET',  '/dasbor',  [DashboardController::class, 'index'], ['auth' => 'user']],
    ['GET', '/jadwal', [\KlinikaID\Clinical\ScheduleController::class, 'index'], ['auth' => 'user']],
    ['GET', '/kunjungan', [\KlinikaID\Clinical\VisitController::class, 'show'], ['auth' => 'user']],
    ['GET', '/kunjungan/riwayat', [\KlinikaID\Clinical\VisitListController::class, 'visits'], ['auth' => 'user']],
    ['GET', '/dokumen/daftar', [\KlinikaID\Clinical\VisitListController::class, 'documents'], ['auth' => 'user']],
    [['GET', 'POST'], '/profil/ubah', [\KlinikaID\Clinical\ProfileController::class, 'edit'], ['auth' => 'public']],
    ['GET', '/dokumen', [\KlinikaID\Clinical\DocumentViewController::class, 'show'], ['auth' => 'user']],
    ['GET', '/dokumen/download', [\KlinikaID\Clinical\DocumentDownloadController::class, 'download'], ['auth' => 'user']],
    ['GET', '/dokumen/hapus', [\KlinikaID\Clinical\DocumentDeleteController::class, 'delete'], ['auth' => 'user']],
    ['GET', '/export', [\KlinikaID\Clinical\ExportController::class, 'export'], ['auth' => 'user']],
    ['GET', '/admin/assignments', [\KlinikaID\Clinical\AdminController::class, 'index'], ['auth' => 'user']],
    ['POST', '/admin/assignments', [\KlinikaID\Clinical\AdminController::class, 'assign'], ['auth' => 'user']],
    ['POST', '/admin/assignments/revoke', [\KlinikaID\Clinical\AdminController::class, 'revoke'], ['auth' => 'user']],
    ['GET',  '/api/me',  [MeController::class, 'show'],         ['auth' => 'user']],
    ['GET',  '/healthz', [HealthController::class, 'show'],     ['auth' => 'public']],
];

<?php
declare(strict_types=1);

$links = [
    'Jadwal Saya' => '/jadwal', 'Jadwal Praktik' => '/jadwal', 'Jadwal Klinik' => '/jadwal',
    'Kunjungan Saya' => '/kunjungan/riwayat', 'Kunjungan Pasien' => '/kunjungan/riwayat', 'Dokumen' => '/dokumen/daftar',
    'Profil' => '/profil/ubah', 'Assignment Dokter' => '/admin/assignments',
];
foreach ($navigation as &$item) {
    $item['href'] ??= $links[$item['label']] ?? null;
}
unset($item);

$activeNavPath = $activePath === '/kunjungan' ? '/kunjungan/riwayat' : $activePath;
use KlinikaID\View\Icon;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->e($title) ?> · KlinikaID</title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body class="<?= $user === null ? 'guest-page' : 'portal-page' ?>">
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="/">KlinikaID</a>
        <?php if ($user === null): ?>
            <span class="header-caption">Portal layanan klinik terpadu</span>
        <?php endif; ?>
        <?php if ($user !== null): ?>
            <div class="account">
                <span class="account-name"><?= $this->e($user->displayName) ?></span>
                <span class="badge"><?= $this->e(\KlinikaID\View\View::roleLabel($user->role)) ?></span>
                <form method="post" action="/logout" class="inline-form">
                    <button type="submit" class="button button-secondary"><?= Icon::svg('logout') ?>Keluar</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($navigation !== []): ?>
        <nav class="site-nav" aria-label="Navigasi utama">
            <ul class="container nav-list">
                <?php $activeNavigationFound = false; ?>
                <?php foreach ($navigation as $item): ?>
                    <li>
                        <?php if ($item['href'] !== null): ?>
                            <?php $isCurrent = !$activeNavigationFound && $activeNavPath === $item['href']; ?>
                            <a href="<?= $this->e($item['href']) ?>"<?= $isCurrent ? ' aria-current="page"' : '' ?>><?= Icon::forNavigation($item['label']) ?><span><?= $this->e($item['label']) ?></span></a>
                            <?php $activeNavigationFound = $activeNavigationFound || $isCurrent; ?>
                        <?php else: ?>
                            <span class="nav-disabled" aria-disabled="true" title="Tersedia pada fase berikutnya"><?= Icon::forNavigation($item['label']) ?><span><?= $this->e($item['label']) ?></span></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    <?php endif; ?>
</header>
<main class="container main">
<?= $content ?>
</main>
<footer class="site-footer">
    <div class="container">KlinikaID · Aplikasi klinik dummy untuk lab secure coding. Seluruh data bersifat fiktif.</div>
</footer>
</body>
</html>

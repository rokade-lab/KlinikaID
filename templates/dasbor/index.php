<?php declare(strict_types=1); use KlinikaID\View\Icon; ?>
<div class="page-heading">
    <p class="eyebrow">PORTAL LAYANAN KLINIK</p>
    <h1>Dasbor <?= $this->e($roleLabel) ?></h1>
    <p>Semua informasi layanan Anda, dalam satu tempat.</p>
</div>
<section class="card hero hero-dasbor">
    <div class="hero-body">
    <span class="eyebrow">SELAMAT DATANG DI KLINIKAID</span>
    <h2>Halo, <span data-testid="display-name"><?= $this->e($user->displayName) ?></span>.</h2>
    <p><?= $clinicalLinks ? 'Pantau jadwal dan riwayat kunjungan Anda. Informasi pemeriksaan kini lebih mudah dijangkau.' : 'Pantau aktivitas layanan dan jadwal klinik untuk mendukung operasional yang lebih teratur.' ?></p>
    <p class="hero-quicklinks"><a class="button" href="/jadwal">Lihat jadwal <span aria-hidden="true">↗</span></a><a href="/profil/ubah">Ubah profil saya <span aria-hidden="true">→</span></a></p>
    </div>
    <div class="hero-media" role="img" aria-label="Dokter mendampingi pasien saat pemeriksaan di klinik"><span class="photo-caption">Mendampingi setiap langkah perawatan.</span></div>
</section>
<section class="overview-grid" aria-label="Ringkasan layanan">
    <div class="overview-item"><span class="overview-icon"><?= Icon::svg('clipboard', 'icon icon-lg') ?></span><div><span class="muted"><?= $clinicalLinks ? 'Ringkasan kunjungan' : 'Ringkasan operasional' ?></span><strong data-testid="visit-count"><?= $this->e($total) ?></strong><span class="small muted">Kunjungan dalam lingkup Anda</span></div></div>
    <a class="overview-item" href="/jadwal"><span class="overview-icon"><?= Icon::svg('calendar', 'icon icon-lg') ?></span><div><strong>Jadwal layanan</strong><span class="muted small">Lihat waktu, poli, dan ruangan</span></div><span class="overview-arrow" aria-hidden="true">↗</span></a>
    <div class="overview-item"><span class="overview-icon"><?= Icon::svg('shield', 'icon icon-lg') ?></span><div><span class="muted small">Akses portal Anda</span><strong data-testid="role"><?= $this->e($roleLabel) ?></strong><span class="small muted">Layanan sesuai peran akun</span></div></div>
</section>
<section class="card">
    <div class="section-heading">
    <div>
    <h2><?= $clinicalLinks ? 'Daftar kunjungan' : 'Aktivitas klinik' ?></h2>
    <p class="muted small">Informasi kunjungan dan lokasi layanan yang tersedia untuk Anda.</p>
    </div><span class="badge"><?= $this->e($total) ?> kunjungan</span>
    </div>
    <form method="get" action="/dasbor">
        <label for="q">Cari poli atau ruangan</label>
        <input id="q" name="q" value="<?= $this->e($search) ?>" maxlength="100" placeholder="Ketik nama poli atau ruangan…">
        <button type="submit" class="button"><?= Icon::svg('search') ?>Cari</button>
    </form>
    <table>
        <thead><tr><th>Waktu</th><th>Poli</th><th>Ruangan</th><th>Status</th><?php if ($clinicalLinks): ?><th>Kunjungan</th><?php endif; ?></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr data-visit-id="<?= $this->e($row['visit_id']) ?>"><td><?= $this->e($row['operational_time']) ?></td><td><?= $this->e($row['department']) ?></td><td><?= $this->e($row['room']) ?></td><td><span class="status-pill"><?= $this->e($row['status']) ?></span></td>
            <?php if ($clinicalLinks): ?><td><a href="/kunjungan?id=<?= $this->e($row['visit_id']) ?>">Detail #<?= $this->e($row['visit_id']) ?></a></td><?php endif; ?></tr>
        <?php endforeach; ?>
        <?php if ($rows === []): ?><tr><td colspan="<?= $clinicalLinks ? '5' : '4' ?>">Belum ada data yang cocok.</td></tr><?php endif; ?>
        </tbody>
    </table>
    <nav aria-label="Halaman kunjungan">
        <?php if ($page > 1): ?><a href="/dasbor?<?= $this->e(http_build_query(['page' => $page - 1, 'q' => $search])) ?>">Sebelumnya</a><?php endif; ?>
        <span>Halaman <?= $this->e($page) ?></span>
        <?php if ($page * 10 < $total): ?><a href="/dasbor?<?= $this->e(http_build_query(['page' => $page + 1, 'q' => $search])) ?>">Berikutnya</a><?php endif; ?>
    </nav>
</section>

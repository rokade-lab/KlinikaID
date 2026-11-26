<?php declare(strict_types=1); use KlinikaID\View\Icon; ?>
<section class="card hero hero-jadwal">
    <div class="hero-body">
    <span class="eyebrow">JADWAL LAYANAN</span>
    <h1>Jadwal Klinik</h1>
    <p>Rencanakan kunjungan dengan lebih mudah. Temukan waktu, poli, dan ruangan layanan Anda di sini.</p>
    <p class="schedule-summary"><span class="badge"><strong data-testid="visit-count"><?= $this->e($total) ?></strong> jadwal</span> dalam lingkup akun Anda</p>
    </div>
    <div class="hero-media" role="img" aria-label="Dokter berkonsultasi dengan pasien"><span class="photo-caption">Lebih terencana, lebih nyaman.</span></div>
</section>
<section class="card">
    <div class="section-heading"><div><h2>Daftar jadwal</h2><p class="muted small">Cari jadwal berdasarkan poli atau ruangan layanan.</p></div><a href="/dasbor">Kembali ke dasbor <span aria-hidden="true">→</span></a></div>
    <form method="get" action="/jadwal">
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
    <nav aria-label="Halaman jadwal">
        <?php if ($page > 1): ?><a href="/jadwal?<?= $this->e(http_build_query(['page' => $page - 1, 'q' => $search])) ?>">Sebelumnya</a><?php endif; ?>
        <span>Halaman <?= $this->e($page) ?></span>
        <?php if ($page * 10 < $total): ?><a href="/jadwal?<?= $this->e(http_build_query(['page' => $page + 1, 'q' => $search])) ?>">Berikutnya</a><?php endif; ?>
    </nav>
</section>

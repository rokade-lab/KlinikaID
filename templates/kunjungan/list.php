<?php
declare(strict_types=1);

use KlinikaID\View\Icon;
?>
<div class="page-heading">
    <p class="eyebrow">RIWAYAT LAYANAN</p>
    <h1><?= $user->role === 'doctor' ? 'Kunjungan Pasien' : 'Kunjungan Saya' ?></h1>
    <p><?= $user->role === 'doctor' ? 'Kunjungan pasien yang sedang ditugaskan kepada Anda.' : 'Riwayat kunjungan dan pemeriksaan Anda di KlinikaID.' ?></p>
</div>
<section class="card">
    <div class="section-heading">
        <div><h2>Daftar kunjungan</h2><p class="muted small">Pilih kunjungan untuk melihat catatan pemeriksaan dan dokumen.</p></div>
        <span class="badge"><?= $this->e($total) ?> kunjungan</span>
    </div>
    <table>
        <thead><tr><th>Tanggal</th><th>Poli</th><th>Ruangan</th><th>Status</th><th>Dokumen</th><th>Kunjungan</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr data-visit-id="<?= $this->e($row['id']) ?>">
                <td><?= $this->e($row['visit_date']) ?></td>
                <td><?= $this->e($row['department']) ?></td>
                <td><?= $this->e($row['room'] ?? '-') ?></td>
                <td><?php if ($row['status'] !== null): ?><span class="status-pill"><?= $this->e($row['status']) ?></span><?php else: ?>-<?php endif; ?></td>
                <td><?= $this->e($row['document_count']) ?></td>
                <td><a class="icon-link" href="/kunjungan?id=<?= $this->e($row['id']) ?>"><?= Icon::svg('eye') ?>Detail #<?= $this->e($row['id']) ?></a></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($rows === []): ?><tr><td colspan="6">Belum ada kunjungan.</td></tr><?php endif; ?>
        </tbody>
    </table>
    <nav aria-label="Halaman kunjungan">
        <?php if ($page > 1): ?><a href="/kunjungan/riwayat?<?= $this->e(http_build_query(['page' => $page - 1])) ?>">Sebelumnya</a><?php endif; ?>
        <span>Halaman <?= $this->e($page) ?></span>
        <?php if ($page * 10 < $total): ?><a href="/kunjungan/riwayat?<?= $this->e(http_build_query(['page' => $page + 1])) ?>">Berikutnya</a><?php endif; ?>
    </nav>
</section>

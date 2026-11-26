<?php
declare(strict_types=1);

use KlinikaID\View\Icon;
?>
<div class="page-heading">
    <p class="eyebrow">DOKUMEN PEMERIKSAAN</p>
    <h1>Dokumen</h1>
    <p>Dokumen PDF dari kunjungan Anda. Pengelolaan dokumen tersedia di halaman detail kunjungan.</p>
</div>
<section class="card">
    <div class="section-heading">
        <div><h2>Daftar dokumen</h2><p class="muted small">Lihat atau unduh dokumen pemeriksaan.</p></div>
        <span class="badge"><?= $this->e($total) ?> dokumen</span>
    </div>
    <table>
        <thead><tr><th>Dokumen</th><th>Tanggal</th><th>Poli</th><th>Ukuran</th><th>Tindakan</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr data-document-id="<?= $this->e($row['id']) ?>">
                <td><span class="icon-link"><?= Icon::svg('file') ?><?= $this->e($row['display_name']) ?></span></td>
                <td><?= $this->e($row['visit_date']) ?></td>
                <td><?= $this->e($row['department']) ?></td>
                <td><?= $this->e(number_format((int) $row['size_bytes'], 0, ',', '.')) ?> byte</td>
                <td>
                    <a class="icon-link" href="/dokumen?id=<?= $this->e($row['id']) ?>"><?= Icon::svg('eye') ?>Lihat</a>
                    · <a class="icon-link" href="/dokumen/download?id=<?= $this->e($row['id']) ?>"><?= Icon::svg('download') ?>Unduh</a>
                    · <a class="icon-link" href="/kunjungan?id=<?= $this->e($row['visit_id']) ?>"><?= Icon::svg('stethoscope') ?>Kunjungan #<?= $this->e($row['visit_id']) ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($rows === []): ?><tr><td colspan="5">Belum ada dokumen.</td></tr><?php endif; ?>
        </tbody>
    </table>
    <nav aria-label="Halaman dokumen">
        <?php if ($page > 1): ?><a href="/dokumen/daftar?<?= $this->e(http_build_query(['page' => $page - 1])) ?>">Sebelumnya</a><?php endif; ?>
        <span>Halaman <?= $this->e($page) ?></span>
        <?php if ($page * 10 < $total): ?><a href="/dokumen/daftar?<?= $this->e(http_build_query(['page' => $page + 1])) ?>">Berikutnya</a><?php endif; ?>
    </nav>
</section>

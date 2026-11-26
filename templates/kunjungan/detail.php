<?php
declare(strict_types=1);

?>
<section class="page-heading">
    <p><a href="/kunjungan/riwayat">&larr; Kembali ke daftar kunjungan</a></p>
    <h1>Detail kunjungan #<?= $this->e($visit['id']) ?></h1>
    <p>Ringkasan layanan dan dokumen pemeriksaan klinik.</p>
</section>
<section class="card" aria-labelledby="visit-heading">
    <h2 id="visit-heading">Informasi kunjungan</h2>
    <dl>
        <dt>Pasien</dt><dd><?= $this->e($visit['full_name']) ?></dd>
        <dt>Nomor rekam medis</dt><dd><?= $this->e($visit['medical_record_no']) ?></dd>
        <dt>Tanggal kunjungan</dt><dd><?= $this->e($visit['visit_date']) ?></dd>
        <dt>Poliklinik</dt><dd><?= $this->e($visit['department']) ?></dd>
    </dl>
    <h2>Catatan pemeriksaan</h2>
    <p data-testid="medical-note"><?= $this->e($visit['medical_note_dummy']) ?></p>
    <?php if ($user !== null && $user->hasRole('patient', 'doctor')): ?>
        <p><a href="/export?visit_id=<?= $this->e($visit['id']) ?>&amp;format=csv">Export CSV</a>
        · <a href="/export?visit_id=<?= $this->e($visit['id']) ?>&amp;format=json">Export JSON</a></p>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../dokumen/list.php'; ?>

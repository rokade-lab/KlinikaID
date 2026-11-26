<?php
declare(strict_types=1);
?>
<div class="auth-split">
<aside class="auth-media" aria-label="Portal layanan klinik">
    <span class="media-label">LEBIH DEKAT DENGAN KESEHATAN ANDA</span>
    <div class="auth-media-text">
        <span class="eyebrow">PERAWATAN YANG TERHUBUNG</span>
        <strong>Pelayanan sepenuh hati.<br>Kemudahan dalam satu portal.</strong>
        <span>Akses jadwal, riwayat kunjungan, dan dokumen pemeriksaan Anda dengan lebih mudah.</span>
        <div class="media-caption">KlinikaID <span>·</span> Portal pasien & tenaga medis</div>
    </div>
</aside>
<section class="card card-narrow">
    <span class="auth-symbol" aria-hidden="true">+</span>
    <p class="eyebrow">SELAMAT DATANG KEMBALI</p>
    <h1>Masuk ke KlinikaID</h1>
    <p class="muted auth-intro">Kelola kebutuhan layanan klinik Anda.<br>Silakan masuk untuk melanjutkan.</p>
    <?php if ($error !== null): ?>
        <div class="alert alert-error" role="alert"><?= $this->e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="/login" class="form" autocomplete="on">
        <label for="username">Nama pengguna</label>
        <input id="username" name="username" type="text" required maxlength="64" autocomplete="username" placeholder="Masukkan nama pengguna" value="<?= $this->e($username) ?>">
        <label for="password">Kata sandi</label>
        <input id="password" name="password" type="password" required maxlength="256" autocomplete="current-password" placeholder="Masukkan kata sandi">
        <button type="submit" class="button">Masuk</button>
    </form>
    <p class="auth-help">Kesulitan masuk? Hubungi pengelola klinik untuk bantuan akun.</p>
    <div class="auth-note"><span aria-hidden="true">✓</span> Gunakan akun yang diberikan pengelola klinik.</div>
</section>
</div>

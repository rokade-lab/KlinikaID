<?php
declare(strict_types=1);
?>
<section class="card card-narrow">
    <h1>Ubah profil</h1>
    <p class="muted">Perbarui nama yang ditampilkan pada layanan KlinikaID.</p>
    <p>Nama pengguna: <strong><?= $this->e($profile['username']) ?></strong></p>
    <form method="post" action="/profil/ubah" class="form">
        <input type="hidden" name="user_id" value="<?= $this->e($profile['id']) ?>">
        <label for="display_name">Nama tampil</label>
        <input id="display_name" name="display_name" value="<?= $this->e($profile['display_name']) ?>" maxlength="200" required>
        <button type="submit" class="button">Simpan profil</button>
    </form>
</section>

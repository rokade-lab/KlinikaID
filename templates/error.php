<?php
declare(strict_types=1);
?>
<section class="card card-narrow error-page">
    <p class="error-code"><?= $this->e($status) ?></p>
    <h1><?= $this->e($heading) ?></h1>
    <p><?= $this->e($message) ?></p>
    <p class="muted small">Kode referensi: <code><?= $this->e($requestId) ?></code></p>
    <p><a class="button" href="<?= $user === null ? '/login' : '/dasbor' ?>"><?= $user === null ? 'Ke halaman masuk' : 'Kembali ke dasbor' ?></a></p>
</section>

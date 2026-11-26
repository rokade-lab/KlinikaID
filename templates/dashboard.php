<?php
declare(strict_types=1);
?>
<section class="card">
    <h1>Dasbor <?= $this->e($roleLabel) ?></h1>
    <p>Selamat datang, <strong data-testid="display-name"><?= $this->e($user->displayName) ?></strong>.</p>
    <p>Anda masuk sebagai <span class="badge" data-testid="role"><?= $this->e($roleLabel) ?></span>.</p>
    <?php if ($upcoming !== []): ?>
        <h2>Menu untuk peran Anda</h2>
        <ul class="menu-list">
            <?php foreach ($upcoming as $item): ?>
                <li><span class="nav-disabled" aria-disabled="true"><?= $this->e($item['label']) ?></span> <span class="muted small">— segera tersedia</span></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

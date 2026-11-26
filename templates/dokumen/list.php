<?php
declare(strict_types=1);

use KlinikaID\View\Icon;
?>
<section class="card" aria-labelledby="documents-heading">
    <h2 id="documents-heading">Dokumen kunjungan</h2>
    <?php if ($documents === []): ?>
        <p>Belum ada dokumen untuk kunjungan ini.</p>
    <?php else: ?>
        <table>
            <thead><tr><th scope="col">Dokumen</th><th scope="col">Ukuran</th><th scope="col">Tindakan</th></tr></thead>
            <tbody>
            <?php foreach ($documents as $document): ?>
                <tr>
                    <td><?= $this->e($document['display_name']) ?></td>
                    <td><?= $this->e(number_format((int) $document['size_bytes'], 0, ',', '.')) ?> byte</td>
                    <td>
                        <a class="icon-link" href="/dokumen?id=<?= $this->e($document['id']) ?>"><?= Icon::svg('eye') ?>Lihat dokumen</a>
                        · <a class="icon-link" href="/dokumen/download?id=<?= $this->e($document['id']) ?>"><?= Icon::svg('download') ?>Unduh dokumen</a>
                        <?php if ($user !== null && $user->role === 'patient' && $user->patientId === (int) $visit['patient_id']): ?>
                            · <a class="icon-link icon-link-danger" href="/dokumen/hapus?id=<?= $this->e($document['id']) ?>"><?= Icon::svg('trash') ?>Hapus dokumen</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

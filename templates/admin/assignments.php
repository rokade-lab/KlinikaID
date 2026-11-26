<?php declare(strict_types=1); ?>
<section class="card">
    <h1>Assignment Dokter</h1>
    <p class="muted">Kelola penugasan dokter untuk kunjungan klinik dummy.</p>
    <form method="post" action="/admin/assignments" class="form">
        <label for="doctor_id">Dokter</label>
        <select name="doctor_id" id="doctor_id" required>
        <?php foreach ($doctors as $doctor): if (!$doctor['active']) continue; ?>
            <option value="<?= $this->e($doctor['id']) ?>"><?= $this->e($doctor['display_name']) ?></option>
        <?php endforeach; ?>
        </select>
        <label for="visit_id">Kunjungan</label>
        <select name="visit_id" id="visit_id" required>
        <?php foreach ($visits as $visit): ?>
            <option value="<?= $this->e($visit['id']) ?>"><?= $this->e($visit['id']) ?> · <?= $this->e($visit['visit_date']) ?> · <?= $this->e($visit['department']) ?></option>
        <?php endforeach; ?>
        </select>
        <button type="submit" class="button">Simpan assignment</button>
    </form>
</section>
<section class="card">
    <h2>Daftar penugasan</h2>
    <table><thead><tr><th>Dokter</th><th>Kunjungan</th><th>Status</th><th>Tindakan</th></tr></thead><tbody>
    <?php foreach ($assignments as $assignment): ?>
        <tr><td><?= $this->e($assignment['display_name']) ?></td><td><?= $this->e($assignment['visit_id']) ?></td><td><?= $assignment['active'] ? 'Aktif' : 'Dicabut' ?></td><td>
        <?php if ($assignment['active']): ?>
            <form method="post" action="/admin/assignments/revoke">
                <input type="hidden" name="doctor_id" value="<?= $this->e($assignment['doctor_id']) ?>">
                <input type="hidden" name="visit_id" value="<?= $this->e($assignment['visit_id']) ?>">
                <button type="submit" class="button button-secondary">Cabut assignment</button>
            </form>
        <?php endif; ?>
        </td></tr>
    <?php endforeach; ?>
    </tbody></table>
</section>

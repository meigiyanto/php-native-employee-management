<?php
$position = $position ?? [];
$errors = $errors ?? [];
$departments = $departments ?? [];
?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <strong>Periksa kembali data berikut:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="mb-3">
    <label for="department_id" class="form-label">Departemen</label>
    <select
        name="department_id"
        id="department_id"
        class="form-select <?= isset($errors['department_id']) ? 'is-invalid' : '' ?>"
        required
    >
        <option value="">-- Pilih Departemen --</option>
        <?php foreach ($departments as $department): ?>
            <option
                value="<?= e($department['id']) ?>"
                <?= (string) ($position['department_id'] ?? '') === (string) $department['id'] ? 'selected' : '' ?>
            >
                <?= e($department['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if (isset($errors['department_id'])): ?>
        <div class="invalid-feedback">
            <?= e($errors['department_id']) ?>
        </div>
    <?php endif; ?>
</div>

<div class="mb-3">
    <label for="name" class="form-label">Nama Jabatan</label>
    <input
        type="text"
        name="name"
        id="name"
        maxlength="100"
        class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
        value="<?= e($position['name'] ?? '') ?>"
        required
    >
    <?php if (isset($errors['name'])): ?>
        <div class="invalid-feedback"><?= e($errors['name']) ?></div>
    <?php endif; ?>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Deskripsi</label>
    <textarea
        name="description"
        id="description"
        rows="4"
        class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
    ><?= e($position['description'] ?? '') ?></textarea>
    <?php if (isset($errors['description'])): ?>
        <div class="invalid-feedback">
            <?= e($errors['description']) ?>
        </div>
    <?php endif; ?>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save me-1"></i> Simpan
    </button>
    <a href="index.php?page=positions" class="btn btn-outline-secondary">
        Batal
    </a>
</div>

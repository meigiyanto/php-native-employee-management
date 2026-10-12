<?php
$isEdit = isset($department['id']);
$pageTitle = $isEdit ? 'Edit Departemen' : 'Tambah Departemen';
?>

<div class="mb-4">
    <a href="index.php?page=departments"
       class="text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke daftar
    </a>
    <h1 class="h3 fw-bold mt-3"><?= e($pageTitle) ?></h1>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <p class="fw-semibold mb-2">Periksa kembali input berikut:</p>
        <ul class="mb-0">
            <?php foreach ($errors as $message): ?>
                <li><?= e($message) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST"
              action="index.php?page=<?= $isEdit ? 'department-update' : 'department-store' ?>">

            <input type="hidden" name="csrf_token"
                   value="<?= e(csrfToken()) ?>">

            <?php if ($isEdit): ?>
                <input type="hidden" name="id"
                       value="<?= (int) $department['id'] ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label for="name" class="form-label">
                    Nama Departemen <span class="text-danger">*</span>
                </label>
                <input type="text" id="name" name="name"
                       class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                       maxlength="100" required
                       value="<?= e($department['name'] ?? '') ?>">
                <?php if (isset($errors['name'])): ?>
                    <div class="invalid-feedback">
                        <?= e($errors['name']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea id="description" name="description"
                          class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                          rows="4"><?= e($department['description'] ?? '') ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <div class="invalid-feedback">
                        <?= e($errors['description']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i> Simpan
            </button>
            <a href="index.php?page=departments"
               class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
$isEdit = $isEdit ?? isset($user['id']);
$formTitle = $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna';
$currentUserId = (int) ($_SESSION['auth_user']['id'] ?? 0);
$isEditingSelf = $isEdit && (int) $user['id'] === $currentUserId;
?>

<div class="mb-4">
    <a href="index.php?page=users" class="text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke daftar pengguna
    </a>

    <h1 class="h3 fw-bold mt-3"><?= e($formTitle) ?></h1>
    <p class="text-secondary">
        <?= $isEdit
            ? 'Perbarui informasi akun dan hak akses.'
            : 'Buat akun baru untuk mengakses aplikasi.' ?>
    </p>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger" role="alert">
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
        <form
            method="POST"
            action="index.php?page=<?= $isEdit ? 'user-update' : 'user-store' ?>"
        >
            <input type="hidden" name="csrf_token"
                   value="<?= e(csrfToken()) ?>">

            <?php if ($isEdit): ?>
                <input type="hidden" name="id"
                       value="<?= (int) $user['id'] ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label for="name" class="form-label">
                    Nama lengkap <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                    maxlength="100"
                    autocomplete="name"
                    required
                    value="<?= e($user['name'] ?? '') ?>"
                >
                <?php if (isset($errors['name'])): ?>
                    <div class="invalid-feedback"><?= e($errors['name']) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">
                    Email <span class="text-danger">*</span>
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                    maxlength="150"
                    autocomplete="email"
                    required
                    value="<?= e($user['email'] ?? '') ?>"
                >
                <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback"><?= e($errors['email']) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">
                    Role <span class="text-danger">*</span>
                </label>
                <select
                    id="role"
                    name="role"
                    class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>"
                    required
                    <?= $isEditingSelf ? 'disabled' : '' ?>
                >
                    <option value="hr"
                        <?= ($user['role'] ?? 'hr') === 'hr' ? 'selected' : '' ?>>
                        HR — Mengelola data organisasi
                    </option>
                    <option value="admin"
                        <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>
                        Admin — Mengelola aplikasi dan pengguna
                    </option>
                </select>

                <?php if ($isEditingSelf): ?>
                    <input type="hidden" name="role" value="admin">
                    <div class="form-text">
                        Role akun yang sedang digunakan tidak dapat diubah di sini.
                    </div>
                <?php endif; ?>

                <?php if (isset($errors['role'])): ?>
                    <div class="text-danger small mt-1"><?= e($errors['role']) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">
                    <?= $isEdit ? 'Password baru' : 'Password' ?>
                    <?= !$isEdit ? '<span class="text-danger">*</span>' : '' ?>
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                    minlength="8"
                    autocomplete="<?= $isEdit ? 'new-password' : 'new-password' ?>"
                    <?= !$isEdit ? 'required' : '' ?>
                >
                <?php if (isset($errors['password'])): ?>
                    <div class="invalid-feedback"><?= e($errors['password']) ?></div>
                <?php endif; ?>

                <div class="form-text">
                    <?= $isEdit
                        ? 'Kosongkan jika tidak ingin mengubah password.'
                        : 'Minimal 8 karakter.' ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>
                <?= $isEdit ? 'Simpan Perubahan' : 'Buat Pengguna' ?>
            </button>

            <a href="index.php?page=users"
               class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>

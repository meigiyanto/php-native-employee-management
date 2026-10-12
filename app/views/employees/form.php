<?php
$isEdit = isset($employee['id']);
$pageTitle = $isEdit ? 'Edit Karyawan' : 'Tambah Karyawan';

$value = static fn(string $key, mixed $default = '') => $employee[$key] ?? $default;

$selectedDepartment = (string) $value('department_id');
$selectedPosition = (string) $value('position_id');
?>

<div class="mb-4">
    <a href="index.php?page=employees"
       class="text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke daftar
    </a>
    <h1 class="h3 fw-bold mt-3"><?= e($pageTitle) ?></h1>
    <p class="text-secondary">Lengkapi informasi karyawan di bawah ini.</p>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <strong>Periksa kembali data berikut:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <form method="POST"
              action="index.php?page=<?= $isEdit ? 'employee-update' : 'employee-store' ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int) $employee['id'] ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="mb-3">
                    <label for="employee_code" class="form-label">
                        Kode Karyawan
                    </label>

                    <?php if ($isEdit): ?>
                        <input
                            type="text"
                            id="employee_code"
                            class="form-control"
                            value="<?= e($value('employee_code')) ?>"
                            readonly
                            disabled
                        >
                        <div class="form-text">
                            Kode karyawan dibuat otomatis dan tidak dapat diubah.
                        </div>
                    <?php else: ?>
                        <input
                            type="text"
                            id="employee_code"
                            class="form-control"
                            value="Dibuat otomatis saat disimpan"
                            readonly
                            disabled
                        >
                        <div class="form-text">
                            Kode akan dibuat setelah data karyawan berhasil disimpan.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Nama Lengkap *</label>
                    <input name="full_name" class="form-control" maxlength="150" required value="<?= e($value('full_name')) ?>">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" maxlength="150" required value="<?= e($value('email')) ?>">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Nomor Telepon</label>
                    <input name="phone" class="form-control" maxlength="25" value="<?= e($value('phone')) ?>">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="gender" class="form-select">
                        <option value="">Pilih jenis kelamin</option>
                        <option value="male" <?= $value('gender') === 'male' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="female" <?= $value('gender') === 'female' ? 'selected' : '' ?>>Perempuan</option>
                        <option value="other" <?= $value('gender') === 'other' ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="birth_date" class="form-control" value="<?= e($value('birth_date')) ?>">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Tanggal Masuk *</label>
                    <input type="date" name="hire_date" class="form-control" required value="<?= e($value('hire_date')) ?>">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Departemen *</label>
                    <select name="department_id" id="department_id" class="form-select" required>                        <option value="">Pilih departemen</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?= (int) $department['id'] ?>"
                                <?= $selectedDepartment === (string) $department['id'] ? 'selected' : '' ?>>
                                <?= e($department['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Jabatan *</label>
                    <select name="position_id" id="position_id" class="form-select" required>
                        <option value="">Pilih jabatan</option>
                        <?php foreach ($positions as $position): ?>
                            <option
                                value="<?= (int) $position['id'] ?>"
                                data-department="<?= (int) $position['department_id'] ?>"
                                <?= $selectedPosition === (string) $position['id'] ? 'selected' : '' ?>>
                                <?= e($position['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">
                        Pilihan jabatan mengikuti departemen yang dipilih.
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Status *</label>
                    <select name="employment_status" class="form-select" required>
                        <?php
                        $statuses = [
                            'active' => 'Aktif',
                            'inactive' => 'Tidak aktif',
                            'on_leave' => 'Cuti',
                            'resigned' => 'Mengundurkan diri',
                        ];
                        ?>
                        <?php foreach ($statuses as $key => $label): ?>
                            <option value="<?= e($key) ?>"
                                <?= $value('employment_status', 'active') === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Gaji (Rp) *</label>
                    <input type="number" name="salary" class="form-control" min="0" max="9999999999999.99" step="0.01" required value="<?= e($value('salary', '0')) ?>">
                </div>

                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-control" rows="3"><?= e($value('address')) ?></textarea>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>
                    <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Karyawan' ?>
                </button>
                <a href="index.php?page=employees" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const department = document.getElementById('department_id');
    const position = document.getElementById('position_id');

    function filterPositions() {
        const departmentId = department.value;
        const currentPosition = position.value;

        for (const option of position.options) {
            if (!option.value) {
                option.hidden = false;
                continue;
            }

            option.hidden = option.dataset.department !== departmentId;
        }

        const selected = position.selectedOptions[0];

        if (selected && selected.value &&
            selected.dataset.department !== departmentId) {
            position.value = '';
        } else if (!currentPosition) {
            position.value = '';
        }
    }

    department.addEventListener('change', filterPositions);
    filterPositions();
});
</script>

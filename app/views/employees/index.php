<?php $pageTitle = 'Data Karyawan'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Data Karyawan</h1>
        <p class="text-secondary mb-0">Kelola informasi karyawan.</p>
    </div>
    <a href="index.php?page=employee-create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Karyawan
    </a>
</div>

<?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-info alert-dismissible fade show">
        <?= e($_SESSION['flash']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div class="card">
    <div class="card-body">

        <form method="GET">
            <input type="hidden" name="page" value="employees">
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="department_id" class="form-label">Departemen</label>
                        <select id="department_id" name="department_id" class="form-select">
                            <option value="">Semua departemen</option>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?= (int) $department['id'] ?>" <?= (string) ($filters['department_id'] ?? '') === (string) $department['id'] ? 'selected' : '' ?>>
                                    <?= e($department['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div calass="form-group">
                        <label for="position_id" class="form-label">Jabatan</label>
                        <select id="position_id" name="position_id" class="form-select">
                            <option value="">Semua jabatan</option>
                            <?php foreach ($positions as $position): ?>
                                <option value="<?= (int) $position['id'] ?>" <?= (string) ($filters['position_id'] ?? '') === (string) $position['id'] ? 'selected' : '' ?>>
                                    <?= e($position['name']) ?> — <?= e($position['department_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <div class="form-group mb-3">
                        <label for="search" class="form-label">Pencarian</label>
                        <input
                            type="search"
                            id="search"
                            name="search"
                            class="form-control"
                            value="<?= e($search) ?>"
                            placeholder="Kode, nama, email, departemen, jabatan..."
                        >
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="employment_status" class="form-label">Status karyawan</label>
                        <select
                            id="employment_status"
                            name="employment_status"
                            class="form-select"
                        >
                            <option value="">Semua status</option>
                            <option value="active"
                                <?= ($filters['employment_status'] ?? '') === 'active' ? 'selected' : '' ?>>
                                Aktif
                            </option>
                            <option value="inactive"
                                <?= ($filters['employment_status'] ?? '') === 'inactive' ? 'selected' : '' ?>>
                                Nonaktif
                            </option>
                            <option value="on_leave"
                                <?= ($filters['employment_status'] ?? '') === 'on_leave' ? 'selected' : '' ?>>
                                Cuti
                            </option>
                            <option value="resigned"
                                <?= ($filters['employment_status'] ?? '') === 'resigned' ? 'selected' : '' ?>>
                                Mengundurkan diri
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary">Cari</button>
                <a href="index.php?page=employees" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive mt-4">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th></th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Departemen</th>
                        <th>Jabatan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$employees): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            Belum ada data karyawan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $i = 1; ?>
                    <?php foreach ($employees as $item): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= e($item['employee_code']) ?></td>
                            <td>
                                <div class="fw-semibold">
                                    <?= e($item['full_name']) ?>
                                </div>
                                <small class="text-secondary">
                                    <?= e($item['email']) ?>
                                </small>
                            </td>
                            <td><?= e($item['department_name']) ?></td>
                            <td><?= e($item['position_name']) ?></td>
                            <td>
                                <?php
                                $badgeClass = match ($item['employment_status']) {
                                    'active' => 'success',
                                    'inactive' => 'secondary',
                                    'on_leave' => 'warning',
                                    'resigned' => 'danger',
                                    default => 'secondary',
                                };
                                ?>
                                <span class="badge text-bg-<?= e($badgeClass) ?>">
                                    <?= e(statusLabel($item['employment_status'])) ?>
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <a class="btn btn-sm btn-outline-primary"
                                   href="index.php?page=employee-show&id=<?= (int) $item['id'] ?>">
                                    Detail
                                </a>
                                <a class="btn btn-sm btn-outline-warning"
                                   href="index.php?page=employee-edit&id=<?= (int) $item['id'] ?>">
                                    Edit
                                </a>
                                <form method="POST"
                                      action="index.php?page=employee-delete"
                                      class="d-inline"
                                      onsubmit="return confirm('Hapus karyawan ini?')">
                                    <input type="hidden" name="csrf_token"
                                           value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="id"
                                           value="<?= (int) $item['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

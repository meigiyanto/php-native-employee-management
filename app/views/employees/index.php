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
        <button type="button" class="btn-close"
                data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<form method="GET" class="row g-2 mb-3">
    <input type="hidden" name="page" value="employees">
    <div class="col-12 col-md-5">
        <input type="search" name="search" class="form-control"
               value="<?= e($search) ?>"
               placeholder="Cari kode, nama, atau email...">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-primary">Cari</button>
        <a href="index.php?page=employees"
           class="btn btn-outline-secondary">Reset</a>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
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
                <?php foreach ($employees as $item): ?>
                    <tr>
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

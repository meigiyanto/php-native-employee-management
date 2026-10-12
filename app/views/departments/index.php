<?php $pageTitle = 'Data Departemen'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Data Departemen</h1>
        <p class="text-secondary mb-0">Kelola departemen organisasi.</p>
    </div>
    <a href="index.php?page=department-create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Departemen
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
    <input type="hidden" name="page" value="departments">
    <div class="col-12 col-md-5">
        <input type="search" name="search" class="form-control"
               value="<?= e($search) ?>"
               placeholder="Cari nama atau deskripsi departemen...">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-primary">Cari</button>
        <a href="index.php?page=departments"
           class="btn btn-outline-secondary">Reset</a>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 text-nowrap">
            <thead class="table-light">
                <tr>
                    <th></th>
                    <th>Nama Departemen</th>
                    <th>Deskripsi</th>
                    <th>Karyawan</th>
                    <th>Jabatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$departments): ?>
                <tr>
                    <td colspan="5" class="text-center py-5">
                        Belum ada data departemen.
                    </td>
                </tr>
            <?php else: ?>
                <?php $i = 1; ?>
                <?php foreach ($departments as $item): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td class="fw-semibold"><?= e($item['name']) ?></td>
                        <td><?= e($item['description'] ?: '-') ?></td>
                        <td><?= (int) $item['employee_count'] ?></td>
                        <td><?= (int) $item['position_count'] ?></td>
                        <td class="text-nowrap">
                            <a class="btn btn-sm btn-outline-primary"
                               href="index.php?page=department-show&id=<?= (int) $item['id'] ?>">
                                Detail
                            </a>
                            <a class="btn btn-sm btn-outline-warning"
                               href="index.php?page=department-edit&id=<?= (int) $item['id'] ?>">
                                Edit
                            </a>

                            <?php if (($_SESSION['auth_user']['role'] ?? '') === 'admin'): ?>
                                <form method="POST"
                                      action="index.php?page=department-delete"
                                      class="d-inline"
                                      onsubmit="return confirm('Hapus departemen ini?')">
                                    <input type="hidden" name="csrf_token"
                                           value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="id"
                                           value="<?= (int) $item['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger">
                                        Hapus
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

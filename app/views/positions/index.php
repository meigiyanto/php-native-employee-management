<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Data Jabatan</h1>
        <p class="text-secondary mb-0">Kelola jabatan pada setiap departemen.</p>
    </div>
    <a href="index.php?page=position-create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Jabatan
    </a>
</div>

<?php if ($flash): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?= e($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <form method="GET" class="row g-2 mb-3">
            <input type="hidden" name="page" value="positions">
            <div class="col">
                <input
                    type="search"
                    name="search"
                    class="form-control"
                    placeholder="Cari jabatan atau departemen..."
                    value="<?= e($search ?? '') ?>"
                >
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary" type="submit">
                    Cari
                </button>
                <a href="index.php?page=positions"
                   class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th></th>
                        <th>Nama Jabatan</th>
                        <th>Departemen</th>
                        <th>Karyawan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($positions)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-secondary py-4">
                            Belum ada data jabatan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $i = 1; ?>
                    <?php foreach ($positions as $position): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td>
                                <div class="fw-semibold">
                                    <?= e($position['name']) ?>
                                </div>
                                <div class="small text-secondary">
                                    <?= e($position['description'] ?: 'Tanpa deskripsi') ?>
                                </div>
                            </td>
                            <td><?= e($position['department_name']) ?></td>
                            <td>
                                <span class="badge text-bg-light border">
                                    <?= e($position['employee_count']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a
                                        href="index.php?page=position-show&id=<?= e($position['id']) ?>"
                                        class="btn btn-sm btn-outline-info"
                                        aria-label="Detail"
                                    ><i class="bi bi-eye"></i></a>

                                    <a
                                        href="index.php?page=position-edit&id=<?= e($position['id']) ?>"
                                        class="btn btn-sm btn-outline-primary"
                                        aria-label="Edit"
                                    ><i class="bi bi-pencil"></i></a>

                                    <?php if (($_SESSION['auth_user']['role'] ?? '') === 'admin'): ?>
                                        <form
                                            method="POST"
                                            action="index.php?page=position-delete"
                                            onsubmit="return confirm('Yakin ingin menghapus jabatan ini?')"
                                        >
                                            <input type="hidden" name="csrf_token"
                                                   value="<?= e(csrfToken()) ?>">
                                            <input type="hidden" name="id"
                                                   value="<?= e($position['id']) ?>">
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                aria-label="Hapus"
                                            ><i class="bi bi-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

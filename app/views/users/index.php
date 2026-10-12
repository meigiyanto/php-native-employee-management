<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Manajemen Pengguna</h1>
        <p class="text-secondary mb-0">
            Kelola akun dan hak akses pengguna aplikasi.
        </p>
    </div>

    <a href="index.php?page=user-create" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i> Tambah Pengguna
    </a>
</div>

<?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?= e($_SESSION['flash']) ?>
        <button type="button" class="btn-close"
                data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <input type="hidden" name="page" value="users">

            <div class="col-12 col-md-5">
                <input
                    type="search"
                    name="search"
                    class="form-control"
                    value="<?= e($search) ?>"
                    placeholder="Cari nama atau email..."
                    aria-label="Cari pengguna"
                >
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search me-1"></i> Cari
                </button>
                <a href="index.php?page=users"
                   class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">#</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Terdaftar</th>
                    <th class="text-end pe-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-secondary">
                        Tidak ada pengguna yang ditemukan.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $index => $item): ?>
                    <?php
                    $isCurrentUser = (int) $item['id']
                        === (int) ($_SESSION['auth_user']['id'] ?? 0);
                    ?>
                    <tr>
                        <td class="ps-3">
                            <?= $startRecord + $index ?>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= e($item['name']) ?></div>
                            <?php if ($isCurrentUser): ?>
                                <span class="text-secondary small">Akun kamu</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($item['email']) ?></td>
                        <td>
                            <?php if ($item['role'] === 'admin'): ?>
                                <span class="badge text-bg-primary">Admin</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">HR</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= e(date(
                                'd-m-Y',
                                strtotime($item['created_at'])
                            )) ?>
                        </td>
                        <td class="text-end pe-3 text-nowrap">
                            <a
                                href="index.php?page=user-edit&id=<?= (int) $item['id'] ?>"
                                class="btn btn-sm btn-outline-primary"
                            >
                                <i class="bi bi-pencil-square"></i>
                                Edit
                            </a>

                            <?php if (!$isCurrentUser): ?>
                                <form
                                    method="POST"
                                    action="index.php?page=user-delete"
                                    class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus akun pengguna ini?');"
                                >
                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= e(csrfToken()) ?>"
                                    >
                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int) $item['id'] ?>"
                                    >
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
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

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3">
        <small class="text-secondary">
            Menampilkan <?= $startRecord ?>–<?= $endRecord ?>
            dari <?= $totalUsers ?> pengguna
        </small>

        <?php if ($totalPages > 1): ?>
            <nav aria-label="Pagination pengguna">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="index.php?page=users&amp;p=<?= $currentPage - 1 ?>&amp;search=<?= urlencode($search) ?>">
                            Sebelumnya
                        </a>
                    </li>

                    <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                        <li class="page-item <?= $pageNumber === $currentPage ? 'active' : '' ?>">
                            <a class="page-link"
                               href="index.php?page=users&amp;p=<?= $pageNumber ?>&amp;search=<?= urlencode($search) ?>">
                                <?= $pageNumber ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="index.php?page=users&amp;p=<?= $currentPage + 1 ?>&amp;search=<?= urlencode($search) ?>">
                            Berikutnya
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

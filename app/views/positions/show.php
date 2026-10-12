<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Detail Jabatan</h1>
        <p class="text-secondary mb-0">Informasi jabatan dan departemennya.</p>
    </div>
    <a
        href="index.php?page=position-edit&id=<?= e($position['id']) ?>"
        class="btn btn-primary"
    >
        <i class="bi bi-pencil me-1"></i> Edit
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-sm-3">Nama Jabatan</dt>
            <dd class="col-sm-9"><?= e($position['name']) ?></dd>

            <dt class="col-sm-3">Departemen</dt>
            <dd class="col-sm-9"><?= e($position['department_name']) ?></dd>

            <dt class="col-sm-3">Deskripsi</dt>
            <dd class="col-sm-9">
                <?= nl2br(e($position['description'] ?: 'Tidak ada deskripsi')) ?>
            </dd>

            <dt class="col-sm-3">Jumlah Karyawan</dt>
            <dd class="col-sm-9"><?= e($position['employee_count']) ?></dd>

            <dt class="col-sm-3">Dibuat</dt>
            <dd class="col-sm-9"><?= e($position['created_at']) ?></dd>

            <dt class="col-sm-3">Diperbarui</dt>
            <dd class="col-sm-9"><?= e($position['updated_at']) ?></dd>
        </dl>
    </div>
    <div class="card-footer bg-white">
        <a href="index.php?page=positions" class="btn btn-outline-secondary">
            Kembali ke Daftar
        </a>
    </div>
</div>

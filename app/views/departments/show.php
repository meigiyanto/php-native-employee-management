<?php $pageTitle = 'Detail Departemen'; ?>

<div class="mb-4">
    <a href="index.php?page=departments"
       class="text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke daftar
    </a>
    <h1 class="h3 fw-bold mt-3">Detail Departemen</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
            <div>
                <h2 class="h4 mb-1"><?= e($department['name']) ?></h2>
                <span class="text-secondary">
                    ID: <?= (int) $department['id'] ?>
                </span>
            </div>
            <a href="index.php?page=department-edit&id=<?= (int) $department['id'] ?>"
               class="btn btn-warning align-self-start">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-6">
                <div class="text-secondary small mb-1">Deskripsi</div>
                <div class="text-break">
                    <?= nl2br(e($department['description'] ?: '-')) ?>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-secondary small mb-1">Jumlah Karyawan</div>
                <div class="fw-semibold">
                    <?= (int) $department['employee_count'] ?>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-secondary small mb-1">Jumlah Jabatan</div>
                <div class="fw-semibold">
                    <?= (int) $department['position_count'] ?>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="text-secondary small mb-1">Dibuat</div>
                <div><?= e($department['created_at']) ?></div>
            </div>
            <div class="col-12 col-md-6">
                <div class="text-secondary small mb-1">Terakhir diperbarui</div>
                <div><?= e($department['updated_at']) ?></div>
            </div>
        </div>
    </div>
</div>

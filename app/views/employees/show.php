
<?php $pageTitle = 'Detail Karyawan'; ?>

<div class="mb-4">
    <a href="index.php?page=employees" class="text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke daftar
    </a>
    <h1 class="h3 fw-bold mt-3">Detail Karyawan</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
            <div>
                <h2 class="h4 mb-1"><?= e($employee['full_name']) ?></h2>
                <span class="text-secondary">
                    <?= e($employee['employee_code']) ?>
                </span>
            </div>
            <a href="index.php?page=employee-edit&id=<?= (int) $employee['id'] ?>"
               class="btn btn-warning align-self-start">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        </div>

        <div class="row g-4">
            <?php
            $fields = [
                'Email' => $employee['email'],
                'Telepon' => $employee['phone'],
                'Jenis Kelamin' => match ($employee['gender'] ?? '') {
                    'male' => 'Laki-laki',
                    'female' => 'Perempuan',
                    'other' => 'Lainnya',
                    default => '-',
                },
                'Tanggal Lahir' => $employee['birth_date'] ?: '-',
                'Tanggal Masuk' => $employee['hire_date'],
                'Departemen' => $employee['department_name'],
                'Jabatan' => $employee['position_name'],
                'Status' => statusLabel($employee['employment_status']),
                'Gaji' => rupiah($employee['salary']),
                'Alamat' => $employee['address'] ?: '-',
            ];
            ?>

            <?php foreach ($fields as $label => $value): ?>
                <div class="col-12 col-md-6">
                    <div class="text-secondary small mb-1">
                        <?= e($label) ?>
                    </div>
                    <div class="fw-medium text-break">
                        <?= e($value) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

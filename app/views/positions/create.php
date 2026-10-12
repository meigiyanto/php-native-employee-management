<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Tambah Jabatan</h1>
        <p class="text-secondary mb-0">Tambahkan jabatan ke departemen.</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="index.php?page=position-store">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrfToken()) ?>"
            >
            <?php require __DIR__ . '/form.php'; ?>
        </form>
    </div>
</div>

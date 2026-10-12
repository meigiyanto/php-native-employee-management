<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">Edit Jabatan</h1>
    <p class="text-secondary mb-0">Perbarui informasi jabatan.</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="index.php?page=position-update">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrfToken()) ?>"
            >
            <input
                type="hidden"
                name="id"
                value="<?= e($position['id']) ?>"
            >
            <?php require __DIR__ . '/form.php'; ?>
        </form>
    </div>
</div>
